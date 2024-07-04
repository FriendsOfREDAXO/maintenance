<?php

// Upgrade from 2.x to 3.x

$addon = rex_addon::get('maintenance');

if ($addon->hasConfig('responsecode')) {
    $addon->setConfig('http_response_code', $adonn->getConfig('responsecode'));
}

if ($addon->hasConfig('ip')) {
    $addon->setConfig('allowed_ips', $addon->getConfig('ip'));
}

if ($addon->hasConfig('frontend_aktiv')) {
    $addon->setConfig('block_frontend', $addon->getConfig('frontend_aktiv') == 'Deaktivieren' ? false : true);
}

if ($addon->hasConfig('redirect_frontend')) {
    $addon->setConfig('redirect_frontend_to_url', $addon->getConfig('redirect_frontend'));
}

if ($addon->hasConfig('redirect_backend')) {
    $addon->setConfig('redirect_backend_to_url', $addon->getConfig('redirect_backend'));
}

if ($addon->hasConfig('backend_aktiv')) {
    $addon->setConfig('block_backend', $addon->getConfig('backend_aktiv') == '1' ? true : false);
}

if ($addon->hasConfig('blockSession')) {
    $addon->setConfig('block_frontend_rex_user', $addon->getConfig('blockSession') == 'Inaktiv' ? false : true);
}

if ($addon->hasConfig('type')) {
    $addon->setConfig('authentification_mode', $addon->getConfig('type') == 'Password' ? 'password' : 'URL');
}

$addon->removeConfig('responsecode');
$addon->removeConfig('ip');
$addon->removeConfig('frontend_aktiv');
$addon->removeConfig('redirect_frontend');
$addon->removeConfig('redirect_backend');
$addon->removeConfig('backend_aktiv');
$addon->removeConfig('blockSession');
$addon->removeConfig('type');
