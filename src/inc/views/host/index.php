<?php require __DIR__ . '/../_header.php'; ?>
        <noscript>JavaScript must be enabled to use this site.</noscript>
        <div id="host">
            <h2>Channel: <span class="game-name"><?php echo $gameName; ?></span></h2>
            <div id="connection-status">Status: <span>Connecting...</span></div>
<?php require '_board.php'; ?>
            <p>Last Number: <span id="last-number"><?php echo $lastLetter; ?><?php echo $lastNumber; ?></span> | Winner: <span class="game-winner"><?php echo $winner; ?></span></p>
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
                    <h4>Automatic Call</h4>
                    <label><input type="checkbox" id="auto-call"> Automatically call a number every <input type="number" id="auto-call-interval" min="20" max="600" value="<?php echo $autoCall; ?>"> seconds.</label>
                </div>
                <div>
                    <h4>Browser Source</h4>
                    <input type="text" id="source-url" readonly value="<?php echo $hostUrl; ?>">
                    <button id="copy-source-url">Copy</button>
                    <p>Use this for your browser source URL in your streaming software.</p>
                </div>
            </div>
        </div>
<?php require __DIR__ . '/../_footer.php'; ?>
