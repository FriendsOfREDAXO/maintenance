<?php
$authentication_mode = rex_config::get('maintenance', 'authentication_mode', '');

// Debug output - remove this later
// echo '<!-- DEBUG: authentication_mode = ' . htmlspecialchars($authentication_mode) . ' -->';

// Show login form only if password mode is selected
// (This fragment is only loaded when maintenance mode is active)
if ('password' === $authentication_mode) { ?>
<div class="maintenance-login">
    <form action="<?= rex_url::base() ?>" method="post">
        <label for="maintenance_secret">Access-Code</label>
        <input name="maintenance_secret" id="maintenance_secret" class="maintenance-pw-input" type="password" placeholder="" autocomplete="off"/>
        <button type="submit" class="maintenance-pw-btn">Login</button>
    </form>
</div>
<?php } ?>
