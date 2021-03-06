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
	<link rel="stylesheet" href="<?php echo $app['basePath']; ?>css/source.min.css?v=<?php echo $app['version']['asset']; ?>">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js?v=<?php echo $app['version']['asset']; ?>"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.3.0/socket.io.slim.js?v=<?php echo $app['version']['asset']; ?>"></script>
	<script src="<?php echo $app['basePath']; ?>js/gamesource.min.js?v=<?php echo $app['version']['asset']; ?>"></script>
    <title>Stream BINGO</title>
</head>
<body class="nojs">
    <div id="main-container">
        <div id="inner-container">
            <h1>Stream <span class="letter-b">B</span><span class="letter-i">I</span><span class="letter-n">N</span><span class="letter-g">G</span><span class="letter-o">O</span></h1>
            <noscript>JavaScript must be enabled to use this site.</noscript>
            <div id="host">
                <h2>Type <code>!play</code> in chat to get your BINGO card!<br>Type <code>bingo</code> in chat when you have a winning card!</h2>
<?php require __DIR__ . '/../_board.php'; ?>
            </div>
            <div id="end-game">
                <h2>Game Over</h2>
                <p id="winner-display">Congratulations, <strong></strong>!</p>
                <p id="restart-countdown">New game in <strong></strong></p>
            </div>
            <div class="bingo-ball template">
                <div class="ball-shine"></div>
                <div class="inner-ball">
                    <div class="letter"></div>
                    <div class="number"></div>
                </div>
            </div>
        </div>
    </div>
    <div id="beta-notice">BETA</div>
    <div id="version"><?php echo $app['version']['string']; ?></div>
</body>
</html>
