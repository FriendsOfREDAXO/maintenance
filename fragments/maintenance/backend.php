<?php
    $maintenanceBackendHeadline = rex_config::get('maintenance', 'maintenance_backend_headline', 'Maintenance / Wartung');
    $maintenanceBackendUpdateIntervalNumber = rex_config::get('maintenance', 'maintenance_backend_update_interval', 60);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="refresh" content="<?= $maintenanceBackendUpdateIntervalNumber > 0 ? $maintenanceBackendUpdateIntervalNumber : '' ?>">
    <title>
        <?php
            if (rex_addon::get('yrewrite')->isAvailable() && null !== rex_yrewrite::getCurrentDomain()?->getName()) {
                echo rex_yrewrite::getCurrentDomain()->getName();
            } else {
                echo rex::getServerName();
            }
        ?> - Maintenance
    </title>
    <style type="text/css">
        html, body {
            height: 100%;
            background-color: #f7f7f7;
        }
        body {
            display: flex;
            align-items: center;
        }
        .maintenance-container {
            max-width: 500px;
            min-width: 300px;
            width: 50%;
            margin: 0 auto;
            color: #999;
            font-family: -apple-system, BlinkMacSystemFont, "Helvetica Neue", Arial, sans-serif;
            font-size: 15px;
            line-height: 1.5;
            text-align: center;
        }
        .maintenance-error a {
            color: #666;
        }
        .maintenance-error a:hover {
            color: #111;
        }
        .maintenance-error-title {
            margin: 0;
            font-size: 40px;
            font-weight: 700;
            color: #5b98d7;
            text-shadow: 0 4px 2px rgba(255, 255, 255, 1);
            line-height: 1.2em;
        }
        .maintenance-error-message {
            padding: 0 20px;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-error">
            <p class="maintenance-error-title"><?= $maintenanceBackendHeadline ?></p>
            <p class="maintenance-error-message">Backend access has been blocked, please contact your administrator.</p>
            <p class="maintenance-error-message">Der Backend-Zugang wurde gesperrt, bitte kontaktieren Sie ihren Administrator.</p>
        </div>
    </div>
</body>
</html>'
