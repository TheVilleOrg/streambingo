<?php require __DIR__ . '/../_header.php'; ?>
		<h2>Welcome to Stream BINGO</h2>
		<p>Stream BINGO is a game made for live streaming.</p>
		<table>
			<tr>
				<th>Game</th>
				<th>Players</th>
				<th></th>
			</tr>
<?php foreach ($games as $game): ?>
			<tr>
				<td><?php echo $game['name']; ?></td>
				<td><?php echo $game['numCards']; ?></td>
				<td><a href="<?php echo $basePath; ?>play/<?php echo $game['name']; ?>">Join</a></td>
			</tr>
<?php endforeach; ?>
		</table>
<?php require __DIR__ . '/../_footer.php'; ?>
