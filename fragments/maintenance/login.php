<?php
$authentication_mode = rex_config::get('maintenance', 'authentication_mode', '');
$maintenance_secret = rex_config::get('maintenance', 'maintenance_secret', '');

// Show login form only if password mode is selected
if ('password' === $authentication_mode) { ?>
<div class="maintenance-login">
    <form action="<?= rex_url::base() ?>" method="post">
    <label for="maintenance_secret">Access-Code</label>
    <input name="maintenance_secret" class="maintenance-pw-input" type="password" placeholder=""/>
    <button type="submit" class="maintenance-pw-btn">Login</button>
    </form>
</div>
<?php } ?>
