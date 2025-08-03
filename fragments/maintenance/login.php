<?php
if ('password' === rex_config::get('maintenance', 'authentification_mode', '') && '' !== rex_config::get('maintenance', 'maintenance_secret', '')) { ?>
<div class="maintenance-login">
    <form action="<?= rex_url::base() ?>" method="post">
    <label for="maintenance_secret">Access-Code</label>
    <input name="maintenance_secret" class="maintenance-pw-input" type="password" placeholder=""/>
    <button type="submit" class="maintenance-pw-btn">Login</button>
    </form>
</div>
<?php } ?>
