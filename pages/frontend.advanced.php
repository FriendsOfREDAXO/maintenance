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

// Load page-specific assets
rex_view::addCssFile($addon->getAssetsUrl('css/ip-addresses.css'));

// Nur für Admins zugänglich
if (!rex::getUser()->isAdmin()) {
    echo rex_view::error($addon->i18n('maintenance_admin_only'));
    return;
}

$form = rex_config_form::factory($addon->getName());

// Editor-Einstellungen
$form->addFieldset($addon->i18n('maintenance_editor_settings_title'));

// Editor festlegen für Benachrichtigungstext
$field = $form->addTextField('editor');
$field->setLabel($addon->i18n('maintenance_editor_label'));
$field->setNotice($addon->i18n('maintenance_editor_notice'));

// Zugriffskontrolle
$form->addFieldset($addon->i18n('maintenance_access_control_title'));

// Liste der erlaubten IP-Adressen
$field = $form->addTextField('allowed_ips');
$field->setLabel($addon->i18n('maintenance_allowed_ips_label'));
$field->setAttribute('class', 'form-control');
$field->setAttribute('id', 'maintenance-allowed-ips');

// Aktuelle IP-Adresse anzeigen
$clientIp = rex_server('REMOTE_ADDR', 'string', '');
$serverIp = rex_server('SERVER_ADDR', 'string', '');

// IP-Adressen als formatierte Liste mit Buttons
$notice = '<div class="ip-addresses">';
$notice .= '<div class="ip-address-row"><span class="ip-label">' . $addon->i18n('maintenance_your_ip') . ':</span> <code class="ip-code">' . $clientIp . '</code>';
$notice .= ' <button class="btn btn-xs btn-primary" type="button" id="maintenance-add-ip"><i class="rex-icon fa-plus"></i> ' . $addon->i18n('maintenance_add_ip') . '</button></div>';
if ($serverIp && $serverIp !== $clientIp) {
    $notice .= '<div class="ip-address-row"><span class="ip-label">' . $addon->i18n('maintenance_server_ip') . ':</span> <code class="ip-code">' . $serverIp . '</code>';
    $notice .= ' <button class="btn btn-xs btn-primary" type="button" id="maintenance-add-server-ip"><i class="rex-icon fa-plus"></i> ' . $addon->i18n('maintenance_add_server_ip') . '</button></div>';
}
$notice .= '</div>';
$notice .= '<div class="help-block" style="margin-top: 10px;">' . $addon->i18n('maintenance_allowed_ips_notice') . '</div>';
$field->setNotice($notice);

// HTTP-Einstellungen
$form->addFieldset($addon->i18n('maintenance_http_settings_title'));

// Silent Mode (nur HTTP-Status, kein Content)
$field = $form->addCheckboxField('silent_mode');
$field->setLabel($addon->i18n('maintenance_silent_mode_label'));
$field->addOption($addon->i18n('maintenance_silent_mode_enable'), 1);
$field->setNotice($addon->i18n('maintenance_silent_mode_notice'));

// Antwortcode
$field = $form->addSelectField('http_response_code');
$field->setLabel($addon->i18n('maintenance_http_response_code_label'));
$select = $field->getSelect();
$select->addOption($addon->i18n('maintenance_http_response_code_503'), '503');
$select->addOption($addon->i18n('maintenance_http_response_code_403'), '403');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit');
$fragment->setVar('title', $addon->i18n('maintenance_settings_title'));
$fragment->setVar('body', $form->get(), false);
?>

<div class="row">
    <div class="col-lg-8">
        <?= $fragment->parse('core/page/section.php') ?>
    </div>
    <div class="col-lg-4">
        <?php include __DIR__ . '/frontend.sidebar.php' ?>
    </div>
</div>

<script type="text/javascript">
$(document).on('rex:ready', function() {
    // Funktion zum Hinzufügen einer IP-Adresse zum Whitelist-Feld
    function addIpToWhitelist(ip) {
        var ipField = $('#maintenance-allowed-ips');
        
        if (!ipField.length) {
            console.error('IP field not found');
            return;
        }
        
        var currentValue = ipField.val().trim();
        
        if (currentValue === '') {
            // Wenn das Feld leer ist, einfach die IP hinzufügen
            ipField.val(ip);
        } else {
            // IP-Adressen als Array verarbeiten und alle Leerzeichen entfernen
            var ips = currentValue.split(',').map(function(ip) {
                return ip.trim();
            }).filter(function(ip) {
                // Leere Einträge filtern
                return ip !== '';
            });
            
            // Prüfen, ob IP bereits enthalten ist
            if (ips.indexOf(ip) === -1) {
                ips.push(ip);
                // Saubere Komma-getrennte Liste ohne unnötige Leerzeichen
                ipField.val(ips.join(','));
            }
        }
    }
    
    // Client-IP-Adresse hinzufügen
    $('#maintenance-add-ip').on('click', function(e) {
        e.preventDefault();
        var currentIp = '<?= rex_server('REMOTE_ADDR', 'string', '') ?>';
        addIpToWhitelist(currentIp);
    });
    
    // Server-IP-Adresse hinzufügen
    $('#maintenance-add-server-ip').on('click', function(e) {
        e.preventDefault();
        var serverIp = '<?= rex_server('SERVER_ADDR', 'string', '') ?>';
        addIpToWhitelist(serverIp);
    });
});
</script>
