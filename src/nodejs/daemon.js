(function() {
  'use strict';

  const config = require('./config.json');

  const {exec} = require('child_process');

  const tmi = require('tmi.js');

  const http = function() {
    if (config.ssl.enabled) {
      const fs = require('fs');
      const options = {
        key: fs.readFileSync(config.ssl.keyfile),
        cert: fs.readFileSync(config.ssl.cert),
        requestCert: false,
        rejectUnauthorized: false
      };

      if (config.ssl.cafile) {
        options.ca = fs.readFileSync(config.ssl.cafile);
      }

      return require('https').createServer(options);
    } else {
      return require('http').Server();
    }
  }();

  const io = require('socket.io')(http);

  const client = new tmi.Client({
    connection: {
      secure: true,
      reconnect: true
    },
    identity: {
      username: config.twitch.username,
      password: config.twitch.password
    }
  });

  client.connect()
  .then(() => {
    console.log('connected to Twitch');
  }).catch((err) => {
    console.error(err);
  });

  const channels = [];
  const chatTimes = {};

  client.on('chat', (channel, userstate, message, self) => {
    if (self) {
      return;
    }

    if (channels.indexOf(channel.substr(1)) === -1) {
      return;
    }

    message = message.trim().toLowerCase();
    if (message === 'bingo' || message === '!bingo' || message === '!play') {
      const now = new Date().getTime();
      if (now - (chatTimes[userstate['user-id']] || 0) < config.twitch.chatTimeout * 1000) {
        return;
      }

      chatTimes[userstate['user-id']] = now;

      console.log(channel, message);

      if (message === 'bingo' || message === '!bingo') {
        callBingo(channel, userstate);
      } else if (message === '!play') {
        joinGame(channel, userstate);
      }
    }
  });

  http.listen(config.port, config.host, () => {
    console.log(`listening on ${config.host}:${config.port}`);
  });

  io.on('connect', (socket) => {
    socket.on('getgame', (token, cb) => {
      exec(`php ${config.phpcli} getgame ${token}`, (err, stdout) => {
        try {
          const data = JSON.parse(stdout);
          if (data.name) {
            socket.join(data.name);

            socket.on('newnumber', (letter, number) => {
              io.to(data.name).emit('newnumber', letter, number);
            });

            socket.on('newgame', () => {
              io.to(data.name).emit('gameended', data.name);
            });

            socket.on('disconnect', () => {
              channels.splice(channels.indexOf(data.name), 1);
              if (channels.indexOf(data.name) === -1) {
                client.part(data.name)
                .then(() => {
                  console.log(`parted #${data.name}`);
                })
                .catch((err) => {
                  console.warn(err);
                });
              }
            });

            if (channels.indexOf(data.name) === -1) {
              client.join(data.name)
              .then(() => {
                console.log(`joined #${data.name}`);
              })
              .catch((err) => {
                console.warn(err);
              });
            }

            channels.push(data.name);

            cb(data.name);
          }
        } catch (e) {
          console.error(e);
        }
      });
    });

    socket.on('play', (userId, gameNames) => {
      socket.join(`user${userId}`);
      gameNames.forEach((gameName) => {
        socket.join(gameName);
      });
    });

    socket.on('joingame', (gameName) => {
      socket.join(gameName);
    });
  });

  function joinGame(channel, user) {
    exec(`php ${config.phpcli} getcard ${user['user-id']} ${user['name']} ${channel.substr(1)}`, () => {
      io.to(`user${user['user-id']}`).emit('newcard', channel.substr(1));
    });
  }

  function callBingo(channel, user) {
    const gameName = channel.substr(1);
    exec(`php ${config.phpcli} submitcard ${user['user-id']} ${gameName}`, (error, stdout) => {
      try {
        const data = JSON.parse(stdout);
        if (data.result) {
          client.say(channel, `Congratulations @${user['display-name']}!`);
          io.to(gameName).emit('winner', user['display-name']);
        } else if(data.result === null) {
          client.say(channel, `@${user['display-name']}, you do not have a BINGO card.`);
        } else {
          client.say(channel, `@${user['display-name']}, your card does not meet the win conditions.`);
        }
      } catch (e) {
        console.error(e);
      }
    });
  }
})();
