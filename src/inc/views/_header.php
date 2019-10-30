<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
	<link rel="stylesheet" href="/bingo/css/bingo.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/js-cookie@beta/dist/js.cookie.min.js"></script>
<?php if (isset($scripts)): ?>
<?php foreach ($scripts as $name): ?>
	<script src="/bingo/js/<?php echo $name; ?>.js"></script>
<?php endforeach; ?>
<?php endif; ?>
    <title>BINGO</title>
</head>
<body>
    <noscript>JavaScript must be enabled</noscript>
    <h1>BINGO</h1>
