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

$config_file = rex_path::coreData('config.yml');
$data = rex_file::getConfig($config_file);
if ($data && !in_array('maintenance', $data['setup_addons'], true)) {
    $data['setup_addons'][] = 'maintenance';
    rex_file::putConfig($config_file, $data);
}
