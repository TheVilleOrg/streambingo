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

  client.on('chat', (channel, userstate, message, self) => {
    if (self) {
      return;
    }

    console.log(channel, message);
    message = message.trim().toLowerCase();
    if (message === 'bingo' || message === '!bingo') {
      callBingo(channel, userstate);
    } else if (message === '!play') {
      joinGame(channel, userstate);
    }
  });

  http.listen(config.port, config.host, () => {
    console.log(`listening on ${config.host}:${config.port}`);
  });

  const channels = [];

  io.on('connect', (socket) => {
    socket.on('creategame', (token, cb) => {
      exec(`php ${config.phpcli} getgame ${token}`, (err, stdout) => {
        let data = JSON.parse(stdout);
        if (data.name) {
          socket.join(data.name);

          socket.on('callnumber', () => {
            callNumber(data.name);
          });

          socket.on('newgame', () => {
            io.to(data.name).emit('newgame');
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
      });
    });
  });

  function callNumber(gameName) {
    exec(`php ${config.phpcli} callnumber ${gameName}`, (err, stdout) => {
      const data = JSON.parse(stdout);
      io.to(gameName).emit('newnumber', data.letter, data.number);
    });
  }

  function joinGame(channel) {
    exec(`php ${config.phpcli} getgameurl ${channel.substr(1)}`, (err, stdout) => {
      let data = JSON.parse(stdout);
      if (data.url) {
        client.say(channel, data.url);
      }
    });
  }

  function callBingo(channel, user) {
    const gameName = channel.substr(1);
    exec(`php ${config.phpcli} submitcard ${user['user-id']} ${gameName}`, (error, stdout) => {
      let data = JSON.parse(stdout);
      if (data.result) {
        client.say(channel, `Congratulations @${user['display-name']}!`);
        io.to(gameName).emit('winner', user['display-name']);
      } else {
        client.say(channel, `@${user['display-name']}, your card does not meet the win conditions.`);
      }
    });
  }
})();
