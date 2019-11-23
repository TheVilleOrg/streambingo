<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
	<link rel="stylesheet" href="<?php echo $app['basePath']; ?>css/main.min.css">
	<link rel="stylesheet" href="<?php echo $app['basePath']; ?>css/source.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.3.0/socket.io.slim.js"></script>
	<script src="<?php echo $app['basePath']; ?>js/gamesource.min.js"></script>
    <title>Stream BINGO</title>
</head>
<body class="nojs">
    <div id="main-container">
        <div id="inner-container">
            <h1>Stream <span class="letter-b">B</span><span class="letter-i">I</span><span class="letter-n">N</span><span class="letter-g">G</span><span class="letter-o">O</span></h1>
            <noscript>JavaScript must be enabled to use this site.</noscript>
            <div id="host">
                <h2>Type <code>!play</code> in chat to get your BINGO card!</h2>
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
