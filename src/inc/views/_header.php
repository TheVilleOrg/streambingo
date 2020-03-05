<?php

/**
 * This file is part of StreamBingo.
 *
 * @copyright (c) 2020, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 3 (GPL-3.0)
 *
 * For full license information, see the LICENSE file included with the source.
 */

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $app['basePath']; ?>apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $app['basePath']; ?>favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo $app['basePath']; ?>favicon-16x16.png">
    <link rel="manifest" href="<?php echo $app['basePath']; ?>site.webmanifest">
    <link rel="mask-icon" href="<?php echo $app['basePath']; ?>safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#00aba9">
    <meta name="theme-color" content="#ffffff">
	<link rel="stylesheet" href="<?php echo $app['basePath']; ?>css/main.min.css?v=<?php echo $app['version']['asset']; ?>">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js?v=<?php echo $app['version']['asset']; ?>"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.3.0/socket.io.slim.js?v=<?php echo $app['version']['asset']; ?>"></script>
<?php if (isset($scripts)): ?>
<?php foreach ($scripts as $name): ?>
	<script src="<?php echo $app['basePath']; ?>js/<?php echo $name; ?>.min.js?v=<?php echo $app['version']['asset']; ?>"></script>
<?php endforeach; ?>
<?php endif; ?>
    <title>Stream BINGO</title>
</head>
<body class="nojs">
    <div id="nav">
        <a href="<?php echo $app['basePath']; ?>" id="logo">B</a>
        <ul>
            <li><a href="<?php echo $app['basePath']; ?>">Home</a></li>
            <li><a href="<?php echo $app['basePath']; ?>play">Play</a></li>
            <li><a href="<?php echo $app['basePath']; ?>host">Host</a></li>
            <li><a href="<?php echo $app['basePath']; ?>leaderboard">Leaderboard</a></li>
            <li><a href="https://discord.io/StreamBingo" target="_blank">Discord</a></li>
            <li><a href="https://twitter.com/streambingolive" target="_blank">Twitter</a></li>
        </ul>
        <span id="user">
<?php if ($app['user']['loggedIn']): ?>
            Welcome, <strong><?php echo $app['user']['name']; ?></strong> | <a href="<?php echo $app['user']['logoutUrl']; ?>">Logout</a>
<?php else: ?>
            <a href="<?php echo $app['authUrl']; ?>" class="twitch-login">Login with Twitch</a>
<?php endif; ?>
        </span>
    </div>
	<div id="main">
		<h1>Stream <span class="letter-b">B</span><span class="letter-i">I</span><span class="letter-n">N</span><span class="letter-g">G</span><span class="letter-o">O</span></h1>
