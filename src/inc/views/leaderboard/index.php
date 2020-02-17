<?php require __DIR__ . '/../_header.php'; ?>
		<h2>Stream <span class="letter-b">B</span><span class="letter-i">I</span><span class="letter-n">N</span><span class="letter-g">G</span><span class="letter-o">O</span> Leaderboard</h2>
        <p>Join our <a href="https://discord.io/StreamBingo" target="_blank">Discord server</a> or <a href="https://twitter.com/streambingolive" target="_blank">follow us on Twitter</a> to stay up to date with Stream BINGO news.</p>
		<table id="game-list">
			<tr>
				<th>Player</th>
				<th>Score</th>
            </tr>
<?php if (empty($records)): ?>
            <tr>
                <td colspan="2">There are no records.</td>
            </tr>
<?php else: ?>
<?php foreach ($records as $record): ?>
			<tr>
				<td><?php echo $record['userName']; ?></td>
				<td class="player-count"><?php echo $record['score']; ?></td>
			</tr>
<?php endforeach; ?>
<?php endif; ?>
		</table>
<?php require __DIR__ . '/../_footer.php'; ?>
