<?php require __DIR__ . '/../_header.php'; ?>
    <p>Last Number: <span id="number"><?php echo $last; ?></span></p>
    <table id="board">
        <tr>
            <th>B</th>
<?php for ($i = 1; $i <= 15; $i++): ?>
			<td<?php if (\in_array($i, $called)): ?> class="marked"<?php endif; ?> data-cell="<?php echo $i; ?>"><?php echo $i; ?></td>
<?php endfor; ?>
        </tr>
        <tr>
            <th>I</th>
<?php for ($i = 16; $i <= 30; $i++): ?>
			<td<?php if (\in_array($i, $called)): ?> class="marked"<?php endif; ?> data-cell="<?php echo $i; ?>"><?php echo $i; ?></td>
<?php endfor; ?>
        </tr>
        <tr>
            <th>N</th>
<?php for ($i = 31; $i <= 45; $i++): ?>
			<td<?php if (\in_array($i, $called)): ?> class="marked"<?php endif; ?> data-cell="<?php echo $i; ?>"><?php echo $i; ?></td>
<?php endfor; ?>
        </tr>
        <tr>
            <th>G</th>
<?php for ($i = 46; $i <= 60; $i++): ?>
			<td<?php if (\in_array($i, $called)): ?> class="marked"<?php endif; ?> data-cell="<?php echo $i; ?>"><?php echo $i; ?></td>
<?php endfor; ?>
        </tr>
        <tr>
            <th>O</th>
<?php for ($i = 61; $i <= 75; $i++): ?>
			<td<?php if (\in_array($i, $called)): ?> class="marked"<?php endif; ?> data-cell="<?php echo $i; ?>"><?php echo $i; ?></td>
<?php endfor; ?>
        </tr>
    </table>
    <button id="call-number">Call Number</button>
	<button id="create-game">New Game</button>
<?php require __DIR__ . '/../_footer.php'; ?>
