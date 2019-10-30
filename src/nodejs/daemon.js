const config = require('./config.json');

const { exec, execSync } = require('child_process');

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

    return require('https').createServer(options, httpHandler);
  } else {
    return require('http').Server(httpHandler);
  }
}();

const io = require('socket.io')(http);

const gameList = new Set(JSON.parse(execSync(`php ${config.phpcli} listgames`))['games']);

const client = new tmi.Client({
	connection: {
		secure: true,
		reconnect: true
	},
	identity: {
		username: config.twitch.username,
		password: config.twitch.password
  },
  channels: Array.from(gameList)
});

client.connect()
.then((data) => {
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

function httpHandler(req, res) {
  if (req.headers['authorization'] !== config.secret) {
    res.writeHead(401).end();

    return;
  }

  let path = req.url.split('/');
  if (path.length === 3 && path[1] === 'game') {
    if (req.method === 'PUT') {
      client.join(path[2])
      .then((data) => {
        gameList.add(path[2]);
        console.log(`joined #${path[2]}`);
      }).catch((err) => {
        console.warn(err);
      });
    } else if (req.method === 'DELETE') {
      client.part(path[2])
      .then((data) => {
        gameList.delete(path[2]);
        console.log(`parted #${path[2]}`);
      }).catch((err) => {
        console.warn(err);
      });
    }
  }

  res.writeHead(200).end();
}

function joinGame(channel, user) {
	exec(`php ${config.phpcli} getgame ${channel.substr(1)}`, (err, stdout, stderr) => {
		let data = JSON.parse(stdout);
		if (data.url) {
			client.say(channel, data.url);
		}
	})
}

function callBingo(channel, user) {
	exec(`php ${config.phpcli} submitcard ${user['user-id']} ${channel.substr(1)}`, (error, stdout, stderr) => {
		let data = JSON.parse(stdout);
		if (data.result) {
      client.say(channel, `Congratulations @${user['display-name']}!`);
      io.to(channel).emit('winner', user['display-name']);
		} else {
			client.say(channel, `@${user['display-name']}, your card does not meet the win conditions.`);
		}
	})
}
