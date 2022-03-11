<?php
// remove maintenance into System-AddOns
$config_file = rex_path::coreData('config.yml');
if ($config = rex_file::get($config_file)) {
    $data = rex_string::yamlDecode($config);
    if (in_array("maintenance", $data['system_addons'])) {
      $data['system_addons'] =  array_filter($data['system_addons'] , fn($e) => !in_array($e, ['mainteance']));
      rex_file::put($config_file, rex_string::yamlEncode($data, 3));
    } 
}
