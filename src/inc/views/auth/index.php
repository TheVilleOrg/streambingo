<?php

/**
 * This file is part of StreamBingo.
 *
 * @copyright (c) 2020, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 3 (GPL-3.0)
 *
 * For full license information, see the LICENSE file included with the source.
 */

?>
<?php require __DIR__ . '/../_header.php'; ?>
        <p>Log in using your Twitch account to access this page.</p>
		<p><a href="<?php echo $app['authUrl']; ?>" class="twitch-login">Login with Twitch</a></p>
<?php require __DIR__ . '/../_footer.php'; ?>
