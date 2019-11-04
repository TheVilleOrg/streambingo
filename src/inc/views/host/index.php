<?php require __DIR__ . '/../_header.php'; ?>
		<p>Status: <span id="status">Connecting...</p>
		<p>Last Number: <span id="number"><?php echo $last; ?></span></p>
<?php require '_board.php'; ?>
		<button id="call-number">Call Number</button>
		<button id="create-game">New Game</button>
<?php require __DIR__ . '/../_footer.php'; ?>
