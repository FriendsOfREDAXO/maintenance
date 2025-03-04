<?php
    $maintenanceFrontendHeadline = rex_config::get('maintenance', 'maintenance_frontend_headline', 'Maintenance / Wartung');
    $maintenanceFrontendUpdateIntervalNumber = rex_config::get('maintenance', 'maintenance_frontend_update_interval', 60);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="refresh" content="<?= $maintenanceFrontendUpdateIntervalNumber > 0 ? $maintenanceFrontendUpdateIntervalNumber : '' ?>">
    <title>
        <?php
            if (rex_addon::get('yrewrite')->isAvailable() && null !== rex_yrewrite::getCurrentDomain()?->getName()) {
                echo rex_yrewrite::getCurrentDomain()->getName();
            } else {
                echo rex::getServerName();
            }
        ?> - Maintenance
    </title>
    <link rel="stylesheet" href="<?= rex_url::addonAssets('maintenance', 'css/maintenance-frontend.css') ?>" />
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-error">
            <p class="maintenance-error-title"><?= $maintenanceFrontendHeadline ?></p>
            <p class="maintenance-error-message">This website is temporarily unavailable.</p>
            <p class="maintenance-error-message" lang="de">Diese Website ist vorübergehend nicht erreichbar.</p>
        </div>
        <?php
        // Subfragment announcement.php ausgeben
        echo $this->subfragment('maintenance/announcement.php');
        echo $this->subfragment('maintenance/login.php');
        echo $this->subfragment('maintenance/reload.php');
        ?>
    </div>
</body>
</html>
