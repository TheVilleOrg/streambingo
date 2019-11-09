<?php require __DIR__ . '/../_header.php'; ?>
		<h2>Channel: <span class="game-name"><?php echo $gameName; ?></span></h2>
        <div id="connection-status">Status: <span>Connecting...</span></div>
		<table id="card" class="grid">
			<tr>
				<th class="letter-b">B</th>
				<th class="letter-i">I</th>
				<th class="letter-n">N</th>
				<th class="letter-g">G</th>
				<th class="letter-o">O</th>
			</tr>
<?php for ($i = 0; $i < 5; $i++): ?>
        	<tr>
<?php for ($j = 0; $j <= 20; $j += 5): ?>
				<td>
					<div class="marker<?php if ($i + $j === 12): ?> free<?php endif; ?><?php if (\in_array($i + $j, $marked)): ?> marked<?php endif; ?>" data-cell="<?php echo $i + $j; ?>"><?php echo $grid[$i + $j]; ?></div>
				</td>
<?php endfor; ?>
	        </tr>
<?php endfor; ?>
	    </table>
<?php require __DIR__ . '/../_footer.php'; ?>
