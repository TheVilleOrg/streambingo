<?php require __DIR__ . '/../_header.php'; ?>
        <h2>Channel: <span class="game-name"><?php echo $gameName; ?></span></h2>
		<div id="connection-status">Status: <span>Connecting...</span></div>
<?php require '_board.php'; ?>
        <p>Last Number: <span id="last-number"><?php echo $lastLetter; ?><?php echo $lastNumber; ?></span></p>
        <div id="game-controls">
            <div>
                <h4>Game Controls</h4>
                <button id="call-number">Call Number</button>
                <button id="create-game">New Game</button>
            </div>
            <div>
                <h4>Browser Source</h4>
                <input type="text" id="source-url" readonly value="<?php echo $hostUrl; ?>">
                <button id="copy-source-url">Copy</button>
            </div>
        </div>
<?php require __DIR__ . '/../_footer.php'; ?>
