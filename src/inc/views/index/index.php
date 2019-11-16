<?php require __DIR__ . '/../_header.php'; ?>
		<h2>Welcome to Stream <span class="letter-b">B</span><span class="letter-i">I</span><span class="letter-n">N</span><span class="letter-g">G</span><span class="letter-o">O</span></h2>
        <p>Stream <span class="letter-b">B</span><span class="letter-i">I</span><span class="letter-n">N</span><span class="letter-g">G</span><span class="letter-o">O</span> is a game made for live streaming. It is currently in beta so there may be bugs.</p>
        <p>Visit one of the Twitch channels below and say <strong>!play</strong> in chat to join the fun!</p>
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
