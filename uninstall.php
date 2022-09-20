<?php
// remove maintenance into System-AddOns
$config_file = rex_path::coreData('config.yml');
$config = rex_file::get($config_file); 
if ($config !== null) {
    $data = rex_string::yamlDecode($config);
    if (in_array("maintenance", $data['setup_addons'],true)) {
      $data['system_addons'] =  array_filter($data['setup_addons'] , fn($e) => !in_array($e, ['maintenance'],true));
      rex_file::put($config_file, rex_string::yamlEncode($data, 3));
    } 
}
