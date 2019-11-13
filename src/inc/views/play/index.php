<?php require __DIR__ . '/../_header.php'; ?>
        <div id="connection-status">Status: <span>Connecting...</span></div>
        <div id="cards">
<?php foreach ($cards as $card): ?>
            <div class="card" data-game-name="<?php echo $card['gameName']; ?>">
                <h2 class="game-name"><?php echo $card['gameName']; ?></h2>
                <table class="grid">
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
                            <div class="marker<?php if ($i + $j === 12): ?> free<?php endif; ?><?php if (\in_array($i + $j, $card['marked'])): ?> marked<?php endif; ?>" data-cell="<?php echo $i + $j; ?>"><?php echo $card['grid'][$i + $j]; ?></div>
                        </td>
<?php endfor; ?>
                    </tr>
<?php endfor; ?>
                </table>
<?php if ($card['gameEnded']): ?>
                <div class="game-over-wrapper">
                    <div class="game-over">
                        <h3>Game Over</h3>
<?php if ($card['gameWinner']): ?>
                        <p>Winner: <span class="game-winner"><?php echo $card['gameWinner']; ?></span></p>
<?php endif; ?>
                        <div class="game-over-buttons">
                            <button class="cancel">Close</button>
                        </div>
                    </div>
                </div>
<?php endif; ?>
            </div>
<?php endforeach; ?>
        </div>
        <div class="card template">
            <h2 class="game-name"></h2>
            <table class="grid">
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
                        <div class="marker<?php if ($i + $j === 12): ?> free<?php endif; ?>" data-cell="<?php echo $i + $j; ?>"></div>
                    </td>
<?php endfor; ?>
                </tr>
<?php endfor; ?>
            </table>
        </div>
        <div class="game-over-wrapper template">
            <div class="game-over">
                <h3>Game Over</h3>
                <p>Winner: <span class="game-winner"></span></p>
                <div class="game-over-buttons">
                    <button class="cancel">Close</button>
                </div>
            </div>
        </div>
        <script type="application/json" id="game-vars">
            {
                "twitchId": "<?php echo $twitchId; ?>"
            }
        </script>
<?php require __DIR__ . '/../_footer.php'; ?>
