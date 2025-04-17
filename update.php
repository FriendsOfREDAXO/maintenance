<?php

$addon = rex_addon::get('maintenance');

// Upgrade from 2.x to 3.x
if (rex_version::compare($addon->getVersion(), '3.0.0-dev', '<')) {
    if ($addon->hasConfig('responsecode')) {
        $addon->setConfig('http_response_code', $addon->getConfig('responsecode'));
    }

    if ($addon->hasConfig('ip')) {
        $addon->setConfig('allowed_ips', $addon->getConfig('ip'));
    }

    if ($addon->hasConfig('frontend_aktiv')) {
        $addon->setConfig('block_frontend', 'Deaktivieren' === $addon->getConfig('frontend_aktiv') ? 0 : 1);
    }

    if ($addon->hasConfig('redirect_frontend')) {
        $addon->setConfig('redirect_frontend_to_url', $addon->getConfig('redirect_frontend'));
    }

    if ($addon->hasConfig('redirect_backend')) {
        $addon->setConfig('redirect_backend_to_url', $addon->getConfig('redirect_backend'));
    }

    if ($addon->hasConfig('backend_aktiv')) {
        $addon->setConfig('block_backend', '1' === $addon->getConfig('backend_aktiv') ? 1 : 0);
    }

    if ($addon->hasConfig('blockSession')) {
        $addon->setConfig('block_frontend_rex_user', 'Inaktiv' === $addon->getConfig('blockSession') ? 0 : 1);
    }

    if ($addon->hasConfig('type')) {
        $addon->setConfig('authentification_mode', 'Password' === $addon->getConfig('type') ? 'password' : 'URL');
    }

    if ($addon->hasConfig('secret')) {
        $addon->setConfig('maintenance_secret', $addon->getConfig('maintenance_secret'));
    }

    $addon->removeConfig('responsecode');
    $addon->removeConfig('ip');
    $addon->removeConfig('frontend_aktiv');
    $addon->removeConfig('redirect_frontend');
    $addon->removeConfig('redirect_backend');
    $addon->removeConfig('backend_aktiv');
    $addon->removeConfig('blockSession');
    $addon->removeConfig('type');
    $addon->removeConfig('secret');
}

// Leerer String ('') und 'URL' werden beide als gültige URL-Authentifizierung betrachtet
$authentification_mode = $addon->getConfig('authentification_mode', '');
if (!in_array($authentification_mode, ['URL', 'password'], true)) {
    // Wenn kein gültiger Modus gesetzt ist, standardmäßig auf URL setzen
    $addon->setConfig('authentification_mode', 'URL');
}

// Überprüfen, ob ein maintenance_secret existiert
if (!$addon->hasConfig('maintenance_secret') || '' === $addon->getConfig('maintenance_secret')) {
    // Falls kein Secret vorhanden, ein neues generieren
    $addon->setConfig('maintenance_secret', bin2hex(random_bytes(16)));
}
