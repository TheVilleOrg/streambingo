<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
	<link rel="stylesheet" href="<?php echo $app['basePath']; ?>css/main.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.3.0/socket.io.slim.js"></script>
<?php if (isset($scripts)): ?>
<?php foreach ($scripts as $name): ?>
	<script src="<?php echo $app['basePath']; ?>js/<?php echo $name; ?>.min.js"></script>
<?php endforeach; ?>
<?php endif; ?>
    <title>Stream BINGO</title>
</head>
<body>
    <div id="nav">
        <a href="<?php echo $app['basePath']; ?>" id="logo">B</a>
        <ul>
            <li><a href="<?php echo $app['basePath']; ?>">Home</a></li>
            <li><a href="<?php echo $app['basePath']; ?>play">Play</a></li>
            <li><a href="<?php echo $app['basePath']; ?>host">Host</a></li>
        </ul>
        <span id="user">
<?php if ($app['user']['loggedIn']): ?>
            Welcome, <strong><?php echo $app['user']['name']; ?></strong>
<?php else: ?>
            <a href="<?php echo $app['authUrl']; ?>" class="twitch-login">Login with Twitch</a>
<?php endif; ?>
        </span>
    </div>
	<div id="main">
		<h1>Stream <span class="letter-b">B</span><span class="letter-i">I</span><span class="letter-n">N</span><span class="letter-g">G</span><span class="letter-o">O</span></h1>
