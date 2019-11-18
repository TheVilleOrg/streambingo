<?php require __DIR__ . '/../_header.php'; ?>
		<h2>Welcome to Stream <span class="letter-b">B</span><span class="letter-i">I</span><span class="letter-n">N</span><span class="letter-g">G</span><span class="letter-o">O</span></h2>
        <p>Stream <span class="letter-b">B</span><span class="letter-i">I</span><span class="letter-n">N</span><span class="letter-g">G</span><span class="letter-o">O</span> is a game made for live streaming. It is currently in beta so there may be bugs.</p>
        <p>Join our <a href="https://discord.io/StreamBingo" target="_blank">Discord server</a> or <a href="https://twitter.com/streambingolive" target="_blank">follow us on Twitter</a> to stay up to date with Stream BINGO news.</p>
        <p>Visit one of the Twitch channels below and say <strong>!play</strong> in chat to join the fun!</p>
		<table id="game-list">
			<tr>
				<th>Game</th>
				<th width="1%">Players</th>
            </tr>
<?php if (empty($games)): ?>
            <tr>
                <td colspan="2">There are no active games.</td>
            </tr>
<?php else: ?>
<?php foreach ($games as $game): ?>
			<tr>
				<td><a href="https://www.twitch.tv/<?php echo $game['name']; ?>" target="_blank"><?php echo $game['name']; ?></a></td>
				<td class="player-count"><?php echo $game['numCards']; ?></td>
			</tr>
<?php endforeach; ?>
<?php endif; ?>
		</table>
<?php require __DIR__ . '/../_footer.php'; ?>
