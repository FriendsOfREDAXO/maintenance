<?php
/**
 * Backend-Wartungsseite
 */

$addon = rex_addon::get('maintenance');
$maintenanceBackendUpdateIntervalNumber = rex_config::get('maintenance', 'maintenance_backend_update_interval', 60);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <?php if ($maintenanceBackendUpdateIntervalNumber > 0): ?>
    <meta http-equiv="refresh" content="<?= $maintenanceBackendUpdateIntervalNumber ?>">
    <?php endif; ?>
    <title><?= rex::getServerName() ?> - <?= $addon->i18n('maintenance_backend_maintenance') ?></title>
    <style>
        :root {
            --primary-color: #c33534;
            --text-color: #555;
            --bg-color: #f8f8f8;
            --card-bg: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--bg-color);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        .maintenance-container {
            max-width: 600px;
            padding: 2rem;
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: var(--shadow);
            text-align: center;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        .maintenance-title {
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
        }
        
        .maintenance-message {
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        
        .maintenance-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            fill: var(--primary-color);
            display: block;
        }
        
        .maintenance-button {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 0.75rem 1.5rem;
            margin-top: 1rem;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s;
            text-decoration: none;
        }
        
        .maintenance-button:hover {
            background-color: #b32a2a;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 600px) {
            .maintenance-container {
                width: 90%;
                padding: 1.5rem;
            }
            
            .maintenance-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <svg class="maintenance-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <line x1="23" y1="11" x2="17" y2="11"></line>
        </svg>
        
        <h1 class="maintenance-title"><?= $addon->i18n('maintenance_backend_maintenance') ?></h1>
        <div class="maintenance-message">
            <p><?= $addon->i18n('maintenance_backend_maintenance_message') ?></p>
            <p><?= $addon->i18n('maintenance_contact_admin') ?></p>
        </div>
        
        <div style="margin-top: 2rem;">
            <?php
            $logoutUrl = rex_url::backendController([
                'rex_logout' => 1,
                '_csrf_token' => rex_csrf_token::factory('backend_logout')->getValue()
            ]);
            ?>
            <a href="<?= $logoutUrl ?>" class="maintenance-button" style="margin-right: 1rem;"><?= $addon->i18n('maintenance_logout') ?></a>
            <a href="<?= rex_url::frontend() ?>" class="maintenance-button"><?= $addon->i18n('maintenance_back_to_website') ?></a>
        </div>
    </div>
</body>
</html>
