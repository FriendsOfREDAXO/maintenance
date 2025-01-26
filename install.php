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

/**
 * Addon in Setup-Addons mit aufnehmen der config.yml in dieser Installation aufnehmen.
 */
$config_file = rex_path::coreData('config.yml');
$data = rex_file::getConfig($config_file);
if (array_key_exists('setup_addons', $data) && !in_array('maintenance', $data['setup_addons'], true)) {
    $data['setup_addons'][] = 'maintenance';
    rex_file::putConfig($config_file, $data);
}

/* Eigene IP-Adresse in die erlaubten IP-Adressen hinzufügen, sofern nicht bereits vorhanden */
$allowed_ips = (string) $addon->getConfig('allowed_ips'); /** @phpstan-ignore-line */
$allowed_ips = array_filter(explode(',', $allowed_ips)); // Leere Elemente entfernen
$ip = rex_server('SERVER_ADDR', 'string', '');

if (!in_array($ip, $allowed_ips, true)) {
    $allowed_ips[] = $ip;
    $addon->setConfig('allowed_ips', implode(',', $allowed_ips));
} else {
    $addon->setConfig('allowed_ips', implode(',', $allowed_ips)); // Sicherstellen, dass keine leeren Werte enthalten sind
}

/* Bei Installation standardmäßig ein zufälliges Secret generieren */
if ('' === $addon->getConfig('maintenance_secret')) {
    $addon->setConfig('maintenance_secret', bin2hex(random_bytes(16)));
}

if ('' === $addon->getConfig('announcement')) {
    $addon->setConfig('announcement', '<p>Geplante Wartungsarbeiten am 01.01.2022 von 00:00 bis 06:00 Uhr. In dieser Zeit ist die Website möglicherweise nicht erreichbar.</p>');
}
