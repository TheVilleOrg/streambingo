		<table id="board" class="grid">
			<tr>
				<th class="letter-b">B</th>
<?php for ($i = 1; $i <= 15; $i++): ?>
				<td data-cell="<?php echo $i; ?>"><?php echo $i; ?></td>
<?php endfor; ?>
			</tr>
			<tr>
				<th class="letter-i">I</th>
<?php for ($i = 16; $i <= 30; $i++): ?>
				<td data-cell="<?php echo $i; ?>"><?php echo $i; ?></td>
<?php endfor; ?>
			</tr>
			<tr>
				<th class="letter-n">N</th>
<?php for ($i = 31; $i <= 45; $i++): ?>
				<td data-cell="<?php echo $i; ?>"><?php echo $i; ?></td>
<?php endfor; ?>
			</tr>
			<tr>
				<th class="letter-g">G</th>
<?php for ($i = 46; $i <= 60; $i++): ?>
				<td data-cell="<?php echo $i; ?>"><?php echo $i; ?></td>
<?php endfor; ?>
			</tr>
			<tr>
				<th class="letter-o">O</th>
<?php for ($i = 61; $i <= 75; $i++): ?>
				<td data-cell="<?php echo $i; ?>"><?php echo $i; ?></td>
<?php endfor; ?>
			</tr>
        </table>
        <div id="game-status">
            <div id="end-countdown" class="hidden">Game Over in <strong></strong></div>
            <div id="card-count"><?php echo $cardCount; ?> Player<?php if ($cardCount !== 1): ?>s<?php endif; ?></div>
        </div>
        <script type="application/json" id="game-vars">
            {
                "gameToken": "<?php echo $gameToken; ?>",
                "cardCount": <?php echo $cardCount; ?>

            }
        </script>
