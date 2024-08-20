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

use FriendsOfREDAXO\Maintenance\MaintenanceUtil;

$addon = rex_addon::get('maintenance');

$form = rex_config_form::factory($addon->getName());

$form->addFieldset($addon->i18n('maintenance_general_title'));

// Aktivierung/Deaktivierung des Wartungsmodus im Frontend
$field = $form->addSelectField('block_frontend');
$field->setLabel($addon->i18n('maintenance_block_frontend_label'));
$select = $field->getSelect();
$select->addOption($addon->i18n('maintenance_block_frontend_false'), 0);
$select->addOption($addon->i18n('maintenance_block_frontend_true'), 1);

// Umgehung der Wartung durch GET-Parameter (URL) oder Passwort
$field = $form->addSelectField('authentification_mode');
$field->setLabel($addon->i18n('maintenance_authentification_mode_label'));
$select = $field->getSelect();
$select->addOption($addon->i18n('maintenance_authentification_mode_url'), 'URL');
$select->addOption($addon->i18n('maintenance_authentification_mode_password'), 'password');

// Blockere auch für angemeldete REDAXO-Benutzer das Frontend
$field = $form->addSelectField('block_frontend_rex_user');
$field->setLabel($addon->i18n('maintenance_block_frontend_rex_user_label'));
$select = $field->getSelect();
$select->addOption($addon->i18n('maintenance_block_frontend_rex_user_false'), 0);
$select->addOption($addon->i18n('maintenance_block_frontend_rex_user_rex_user'), 1);

// Passwort zum Umgehen des Wartungsmodus
$field = $form->addTextField('maintenance_secret');
$field->setLabel($addon->i18n('maintenance_secret_label'));
$field->setNotice($addon->i18n('maintenance_secret_notice', bin2hex(random_bytes(16))));
$field->setAttribute('type', 'password');

// Ziel der Umleitung
$field = $form->addTextField('redirect_frontend_to_url');
$field->setLabel($addon->i18n('maintenance_redirect_frontend_to_url_label'));
$field->setNotice($addon->i18n('maintenance_redirect_frontend_to_url_notice'));
$field->setAttribute('type', 'url');

// Antwortcode
$field = $form->addSelectField('http_response_code');
$field->setLabel($addon->i18n('maintenance_http_response_code_label'));
$select = $field->getSelect();
$select->addOption($addon->i18n('maintenance_http_response_code_503'), '503');
$select->addOption($addon->i18n('maintenance_http_response_code_403'), '403');

// Ausnahmeregeln

$form->addFieldset($addon->i18n('maintenance_allowed_access_title'));

// Erlaubte IP-Adressen
$field = $form->addTextField('allowed_ips');
$field->setLabel($addon->i18n('maintenance_allowed_ips_label'));
$field->setNotice($addon->i18n('maintenance_allowed_ips_notice', \rex_server('REMOTE_ADDR', 'string', ''), \rex_server('SERVER_ADDR', 'string', '')));
$field->setAttribute('class', 'form-control');
$field->setAttribute('data-maintenance', 'tokenfield');
$field->setAttribute('data-beautify', 'false');


// Wenn YRewrite installiert, dann erlaubte YRewrite-Domains auswählen
if (\rex_addon::get('yrewrite')->isAvailable() && count(\rex_yrewrite::getDomains()) > 1) {
    $field = $form->addSelectField('allowed_yrewrite_domains');
    $field->setAttribute('multiple', 'multiple');

    $field->setAttribute('class', 'form-control selectpicker');
    $field->setAttribute('data-live-search', 'true');
    $field->setLabel($addon->i18n('maintenance_allowed_yrewrite_domains_label'));
    $field->setNotice($addon->i18n('maintenance_allowed_yrewrite_domains_notice'));
    $select = $field->getSelect();
    foreach (\rex_yrewrite::getDomains() as $key => $domain) {
        $select->addOption($key, $key);
    }
}
// Erlaubte Domains
$field = $form->addTextField('allowed_domains');
$field->setLabel($addon->i18n('maintenance_allowed_domains_label'));
$field->setNotice($addon->i18n('maintenance_allowed_domains_notice'));
$field->setAttribute('class', 'form-control');
$field->setAttribute('data-maintenance', 'tokenfield');
$field->setAttribute('data-beautify', 'false');

// Wartungsfenster-Ankündigung

$form->addFieldset($addon->i18n('maintenance_announcement_title'));

// Benachrichtigungstext
$field = $form->addTextAreaField('announcement');
$field->setLabel($addon->i18n('maintenance_announcement_label'));
$field->setNotice($addon->i18n('maintenance_announcement_notice'));
if (strval(rex_config::get('maintenance', 'editor')) !== '') { // @phpstan-ignore-line
    $field->setAttribute('class', '###maintenance-settings-editor###');
}

// Editor festlegen für Benachrichtigungstext
$field = $form->addTextField('editor');
$field->setLabel($addon->i18n('maintenance_editor_label'));
$field->setNotice($addon->i18n('maintenance_editor_notice'));

// Start- und Endzeitpunkt der Wartungsankündigung
$field = $form->addTextField('announcement_start_date');
$field->setLabel($addon->i18n('maintenance_announcement_start_date_label'));
$field->setNotice($addon->i18n('maintenance_announcement_start_date_notice', date('Y-m-d H:i:s')));
$field->setAttribute('type', 'datetime-local');

$field = $form->addTextField('announcement_end_date');
$field->setLabel($addon->i18n('maintenance_announcement_end_date_label'));
$field->setNotice($addon->i18n('maintenance_announcement_end_date_notice', date('Y-m-d H:i:s')));
$field->setAttribute('type', 'datetime-local');


$fragment = new rex_fragment();
$fragment->setVar('class', 'edit');
$fragment->setVar('title', $addon->i18n('maintenance_settings_frontend_title'));
$fragment->setVar('body', $form->get(), false);
?>

<div class="row">
	<div class="col-lg-8">
		<?= $fragment->parse('core/page/section.php') ?>
	</div>
	<div class="col-lg-4">


		<?php
/* Vorschau des Wartungsmodus */
$preview = '<a target="_blank" href="' . rex_url::backendPage('maintenance/preview') . '" class="btn btn-primary">' . rex_i18n::msg('maintenance_preview') . '</a>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'info', false);
$fragment->setVar('title', rex_i18n::msg('maintenance_preview_title'), false);
$fragment->setVar('body', $preview, false);
echo $fragment->parse('core/page/section.php');

/* Kopieren der URL für den Wartungsmodus */
        $copy = '<ul class="list-group">';
$url = '' . rex::getServer() . '?maintenance_secret=' . rex_config::get('maintenance', 'maintenance_secret');
$copy .= '<li class="list-group-item"><label for="maintenance-mode-url">REDAXO config.yml</label>
<div class="hidden" id="maintenance-mode-url"><code>' . $url . '</code></div>';
$copy .= '
<clipboard-copy for="maintenance-mode-url" class="input-group">
  <input type="text" value="' . $url . '" readonly class="form-control">
  <span class="input-group-addon"><i class="rex-icon fa-clone"></i></span>
</clipboad-copy></li>';

// Ebenfalls für alle YRewrite-Domains ausgeben

if (\rex_addon::get('yrewrite')->isAvailable() && count(\rex_yrewrite::getDomains()) > 1) {
    foreach (\rex_yrewrite::getDomains() as $key => $domain) {
        if($key == 'default') {
            continue;
        }
        $url = $domain->getUrl() . '?maintenance_secret=' . rex_config::get('maintenance', 'maintenance_secret');
        $copy .= '<li class="list-group-item"><label for="maintenance-mode-url-'.$key.'">YRewrite '. $key . '</label>';
        $copy .= '<div class="hidden" id="maintenance-mode-url-' . $key . '"><code>' . $url . '</code></div>';
        $copy .= '
        <clipboard-copy for="maintenance-mode-url-'.$key.'" class="input-group">
          <input type="text" value="' . $url . '" readonly class="form-control">
          <span class="input-group-addon"><i class="rex-icon fa-clone"></i></span>
        </clipboad-copy></li>';
    }
}

$copy .= '</ul>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'info', false);
$fragment->setVar('title', rex_i18n::msg('maintenance_copy_url_title'), false);
$fragment->setVar('body', $copy, false);
echo $fragment->parse('core/page/section.php');

?>
	</div>
</div>
