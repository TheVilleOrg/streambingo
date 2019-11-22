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
        ca: fs.readFileSync(config.ssl.cafile)
      };

      return require('https').createServer(options, httpHandler);
    } else {
      return require('http').Server(httpHandler);
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
    http.listen(config.port, config.host, () => {
      console.log(`listening on ${config.host}:${config.port}`);
    });
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

      console.log(`[${channel}] ${userstate['username']} ${message}`);

      if (message === 'bingo' || message === '!bingo') {
        callBingo(channel, userstate);
      } else if (message === '!play') {
        joinGame(channel, userstate);
      }
    }
  });

  io.on('connect', (socket) => {
    socket.on('getgame', (token, cb) => {
      exec(`php ${config.phpcli} getgame ${token}`, (err, stdout) => {
        try {
          const data = JSON.parse(stdout);
          if (data.name) {
            socket.join(data.name);
            socket.join(`admin_${data.name}`);

            socket.on('disconnect', () => {
              channels.splice(channels.indexOf(data.name), 1);
              if (channels.indexOf(data.name) === -1) {
                client.part(data.name)
                .then(() => {
                  console.log(`parted Twitch channel #${data.name}`);
                })
                .catch((err) => {
                  console.warn(err);
                });
              }
            });

            if (channels.indexOf(data.name) === -1) {
              client.join(data.name)
              .then(() => {
                console.log(`joined Twitch channel #${data.name}`);
              })
              .catch((err) => {
                console.warn(err);
              });
            }

            channels.push(data.name);

            if (typeof cb === 'function') {
              cb(data.name, data.ended, data.winner);
            }
          }
        } catch (e) {
          console.error(e);
        }
      });
    });

    socket.on('playgame', (token, gameNames, cb) => {
      exec(`php ${config.phpcli} getuser ${token}`, (err, stdout) => {
        try {
          const data = JSON.parse(stdout);
          if (data.userId) {
            socket.join(`user_${data.userId}`);
            gameNames.forEach((gameName) => {
              socket.join(gameName);
            });

            socket.on('joingame', (gameName) => {
              socket.join(gameName);
            });

            if (typeof cb === 'function') {
              cb();
            }
          }
        } catch (e) {
          console.error(e);
        }
      });
    });
  });

  function httpHandler(req, res) {
    if (req.headers['authorization'] !== config.secret) {
      res.writeHead(401).end();
      return;
    }

    if (req.method === 'POST') {
      let body = [];
      req.on('error', (err) => {
        console.error(err);
        res.writeHead(500).end();
      }).on('data', (chunk) => {
        body.push(chunk);
      }).on('end', () => {
        body = Buffer.concat(body).toString();
        try {
          const data = JSON.parse(body);
          switch (data.action) {
            case 'resetGame':
              resetGame(data.gameName, data.gameId);
              break;
            case 'callNumber':
              callNumber(data.gameName, data.letter, data.number);
              break;
            case 'updateGameSettings':
              updateGameSettings(data.gameName, data.settings);
              break;
            default:
              res.writeHead(400).end();
              return;
          }

          res.writeHead(200).end();
        } catch (e) {
          console.warn(e);
          res.writeHead(400).end();
        }
      });

      return;
    }

    res.writeHead(200).end();
  }

  function resetGame(gameName, gameId) {
    io.to(gameName).emit('gameover', gameId);
    io.to(`admin_${gameName}`).emit('resetgame');

    console.log(`created new game ${gameName}`);
  }

  function callNumber(gameName, letter, number) {
    io.to(`admin_${gameName}`).emit('numbercalled', letter, number);

    console.log(`called ${letter}${number} for game ${gameName}`);
  }

  function updateGameSettings(gameName, settings) {
    io.to(`admin_${gameName}`).emit('gamesettings', settings);
  }

  function joinGame(channel, user) {
    const gameName = channel.substr(1);
    exec(`php ${config.phpcli} getcard ${user['user-id']} ${user['username']} ${gameName}`, (err, stdout) => {
      try {
        const data = JSON.parse(stdout);
        if (data.gameId) {
          io.to(`user_${data.userId}`).emit('newcard', data.gameId);
          io.to(`admin_${gameName}`).emit('addplayer');

          console.log(`player ${user['username']} joined game ${gameName}`);
        }

        client.say(channel, `@${user['display-name']} see your BINGO card at ${data.url}`);
      } catch (e) {
        console.error(e);
        console.error(stdout);
      }
    });
  }

  function callBingo(channel, user) {
    const gameName = channel.substr(1);
    exec(`php ${config.phpcli} submitcard ${user['user-id']} ${gameName}`, (err, stdout) => {
      try {
        const data = JSON.parse(stdout);
        if (data.result) {
          client.say(channel, `Congratulations @${user['display-name']}!`);
          io.to(gameName).emit('gameover', data.gameId, user['username']);

          console.log(`player ${user['username']} won game ${gameName}`);
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
