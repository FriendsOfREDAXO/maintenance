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

// Erlaubte IP-Adressen
$field = $form->addTextField('allowed_ips');
$field->setLabel($addon->i18n('maintenance_allowed_ips_label'));

$remoteAddr = rex_server('REMOTE_ADDR', 'string', '');
$serverAddr = rex_server('SERVER_ADDR', 'string', '');

$ipButtons = '';
if ($remoteAddr) {
    $ipButtons .= '<button type="button" class="btn btn-xs btn-default" data-add-ip="' . rex_escape($remoteAddr) . '">';
    $ipButtons .= '<i class="fa fa-plus"></i> ' . rex_escape($remoteAddr) . ' ' . $addon->i18n('maintenance_add_ip_hint');
    $ipButtons .= '</button> ';
}
if ($serverAddr && $serverAddr !== $remoteAddr) {
    $ipButtons .= '<button type="button" class="btn btn-xs btn-default" data-add-ip="' . rex_escape($serverAddr) . '">';
    $ipButtons .= '<i class="fa fa-plus"></i> ' . rex_escape($serverAddr) . ' ' . $addon->i18n('maintenance_add_ip_hint');
    $ipButtons .= '</button>';
}

$field->setNotice($ipButtons . '<br>' . $addon->i18n('maintenance_allowed_ips_notice'));
$field->setAttribute('class', 'form-control');
$field->setAttribute('data-maintenance', 'tokenfield');
$field->setAttribute('data-beautify', 'false');

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

<script nonce="<?= rex_response::getNonce() ?>">
jQuery(function($) {
    // Warte bis Tokenfield initialisiert ist
    function waitForTokenfield(callback, maxAttempts = 20) {
        var attempts = 0;
        var interval = setInterval(function() {
            var $tokenfield = $('[name="config[maintenance][allowed_ips]"]');
            if ($tokenfield.data('bs.tokenfield') || attempts >= maxAttempts) {
                clearInterval(interval);
                callback($tokenfield);
            }
            attempts++;
        }, 100);
    }

    // IP per Klick zur Tokenfield-Liste hinzufügen
    $(document).on('click', '[data-add-ip]', function(e) {
        e.preventDefault();
        var ip = $(this).data('add-ip');
        var $button = $(this);
        
        waitForTokenfield(function($tokenfield) {
            if (!$tokenfield.length) {
                console.error('Tokenfield not found');
                return;
            }

            // Hole aktuelle Tokens
            var tokens = [];
            if ($tokenfield.data('bs.tokenfield')) {
                tokens = $tokenfield.tokenfield('getTokens');
            } else {
                var currentValue = $tokenfield.val();
                tokens = currentValue ? currentValue.split(',').map(function(s) { return s.trim(); }).filter(Boolean) : [];
            }
            
            // Prüfen, ob IP bereits existiert
            if (tokens.indexOf(ip) === -1) {
                tokens.push(ip);
                
                // Tokenfield aktualisieren
                if ($tokenfield.data('bs.tokenfield')) {
                    $tokenfield.tokenfield('setTokens', tokens);
                } else {
                    $tokenfield.val(tokens.join(', '));
                }
                
                // Button-Feedback
                $button.prop('disabled', true).addClass('btn-success').removeClass('btn-default btn-xs')
                    .html('<i class="fa fa-check"></i> ' + ip + ' <?= $addon->i18n('maintenance_ip_added') ?>');
            } else {
                // IP existiert bereits
                $button.prop('disabled', true).addClass('btn-warning').removeClass('btn-default btn-xs')
                    .html('<i class="fa fa-info-circle"></i> IP bereits vorhanden');
            }
        });
    });
});
</script>
