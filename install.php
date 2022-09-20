<?php

/**
 * This file is part of the maintenance package.
 *
 * @author (c) Friends Of REDAXO
 * @author <friendsof@redaxo.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$addon = rex_addon::get('maintenance');
if (!$addon->hasConfig()) {
    $addon->setConfig('ip', '');
    $addon->setConfig('frontend_aktiv', 'Deaktivieren');
    $addon->setConfig('redirect_frontend', '');
    $addon->setConfig('redirect_backend', '');
    $addon->setConfig('backend_aktiv', '0');
    $addon->setConfig('blockSession', 'Inaktiv');
    $addon->setConfig('secret', '');
}

// Write maintenance to setup addOns system config
$config_file = rex_path::coreData('config.yml');
$config = rex_file::get($config_file);
if ($config !== null) {
    $data = rex_string::yamlDecode($config);
    if (in_array("maintenance", $data['setup_addons'], true)) {
    } else {
        $data['setup_addons'][] = 'maintenance';
        rex_file::put($config_file, rex_string::yamlEncode($data, 3));
    }
}
