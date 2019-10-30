<?php require __DIR__ . '/../_header.php'; ?>
	<h2>Game Name: <?php echo $gameName; ?></h2>
	<table id="card">
        <tr>
            <th>B</th>
            <th>I</th>
            <th>N</th>
            <th>G</th>
            <th>O</th>
        </tr>
<?php for ($i = 0; $i < 5; $i++): ?>
        <tr>
<?php for ($j = 0; $j <= 20; $j += 5): ?>
			<td data-cell="<?php echo $i + $j; ?>"<?php if (\in_array($i + $j, $marked)): ?> class="marked"<?php endif; ?>><?php echo $grid[$i + $j]; ?></td>
<?php endfor; ?>
        </tr>
<?php endfor; ?>
    </table>
<?php require __DIR__ . '/../_footer.php'; ?>
