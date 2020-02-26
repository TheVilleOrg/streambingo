<?php require __DIR__ . '/../_header.php'; ?>
        <noscript>JavaScript must be enabled to use this site.</noscript>
        <div id="host">
            <h2>Channel: <span class="game-name"><?php echo $gameName; ?></span></h2>
            <div id="connection-status">Status: <span>Connecting...</span></div>
<?php require '_board.php'; ?>
            <p>Last Number: <span id="last-number">--</span> | Winner: <span class="game-winner">--</span></p>
            <div id="game-controls">
                <div>
                    <h4>Game Controls</h4>
                    <button id="call-number" disabled>Call Number</button>
                    <button id="create-game" disabled>New Game</button>
                </div>
                <div>
                    <h4>Text-To-Speech</h4>
                    <label><input type="checkbox" id="tts"<?php if ($tts): ?> checked<?php endif; ?>> Enable Text-To-Speech</label><br>
                    <label for="tts-voice">Voice: </label>
                    <select id="tts-voice">
<?php foreach ($ttsVoices as $k => $v): ?>
                        <option value="<?php echo $k; ?>"<?php if ($k === $ttsVoice): ?> selected<?php endif; ?>><?php echo $v; ?></option>
<?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <h4>Automation</h4>
                    <label><input type="checkbox" id="auto-call"> Automatically call a number every <input type="number" id="auto-call-interval" min="10" max="600" value="<?php echo $autoCall; ?>"> seconds.</label><br>
                    <label><input type="checkbox" id="auto-restart"> Automatically restart the game <input type="number" id="auto-restart-interval" min="30" max="600" value="<?php echo $autoRestart; ?>"> seconds after ending.</label><br>
                    <label><input type="checkbox" id="auto-end"> Automatically end the game <input type="number" id="auto-end-interval" min="30" max="600" value="<?php echo $autoEnd; ?>"> seconds after the last number has been called.</label>
                </div>
                <div>
                    <h4>Browser Source</h4>
                    <input type="text" id="source-url" readonly value="<?php echo $hostUrl; ?>">
                    <button id="copy-source-url">Copy</button>
                    <p>Use this for your browser source URL in your streaming software.</p>
                </div>
                <div>
                    <h4>Browser Source Background</h4>
                    <label for="background">Background: </label>
                    <select id="background">
<?php foreach ($backgrounds as $k => $v): ?>
                        <option value="<?php echo $k; ?>"<?php if ($k === $background): ?> selected<?php endif; ?>><?php echo $v; ?></option>
<?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
<?php require __DIR__ . '/../_footer.php'; ?>
