<?php require __DIR__ . '/../_header.php'; ?>
		<h2>Welcome to Stream BINGO</h2>
        <p>Stream BINGO is a game made for live streaming.</p>
		<table id="game-list">
			<tr>
				<th>Game</th>
				<th width="1%">Players</th>
			</tr>
<?php foreach ($games as $game): ?>
			<tr>
				<td><a href="https://www.twitch.tv/<?php echo $game['name']; ?>" target="_blank"><?php echo $game['name']; ?></a></td>
				<td class="player-count"><?php echo $game['numCards']; ?></td>
			</tr>
<?php endforeach; ?>
		</table>
<?php require __DIR__ . '/../_footer.php'; ?>
