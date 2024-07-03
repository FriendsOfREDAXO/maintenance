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
$maintenance_functions = new MaintenanceUtil();

$form = rex_config_form::factory($addon->getName());

// Aktivierung/Deaktivierung des Wartungsmodus im Frontend
$field = $form->addSelectField('block_frontend');
$field->setLabel($addon->i18n('maintenance_block_frontend_label'));
$select = $field->getSelect();
$select->addOption($addon->i18n('maintenance_block_frontend_false'), false);
$select->addOption($addon->i18n('maintenance_block_frontend_true'), true);


// Blockere auch für angemeldete REDAXO-Benutzer das Frontend 
$field = $form->addSelectField('block_frontend_rex_user');
$field->setLabel($addon->i18n('maintenance_block_frontend_rex_user_label'));
$select = $field->getSelect();
$select->addOption($addon->i18n('maintenance_block_frontend_rex_user_true'), true);
$select->addOption($addon->i18n('maintenance_block_frontend_rex_user_rex_user'), false);


// Passwort zum Umgehen des Wartungsmodus
$field = $form->addTextField('secret');
$field->setLabel($addon->i18n('maintenance_secret_label'));
$field->setNotice($addon->i18n('maintenance_secret_notice'));

// Erlaubte IP-Adressen
$field = $form->addTextField('allowed_ips');
$field->setLabel($addon->i18n('maintenance_allowed_ips_label'));
$field->setNotice($addon->i18n('maintenance_allowed_ips_notice'));
$field->setAttribute('class', 'form-control selectpicker');

// Erlaubte Domains
$field = $form->addTextField('allowed_domains');
$field->setLabel($addon->i18n('maintenance_allowed_domains_label'));
$field->setNotice($addon->i18n('maintenance_allowed_domains_notice'));
$field->setAttribute('class', 'form-control selectpicker');

// Ziel der Umleitung
$field = $form->addTextField('redirect_frontend_url');
$field->setLabel($addon->i18n('maintenance_redirect_frontend_url_label'));
$field->setNotice($addon->i18n('maintenance_redirect_frontend_url_notice'));

// Umgehung der Wartung durch GET-Parameter (URL) oder Passwort
$field = $form->addSelectField('type');
$field->setLabel($addon->i18n('maintenance_type_label'));
$select = $field->getSelect();
$select->addOption($addon->i18n('maintenance_type_url'), 'URL');
$select->addOption($addon->i18n('maintenance_type_password'), 'password');

// Antwortcode
$field = $form->addSelectField('http_response_code');
$field->setLabel($addon->i18n('maintenance_http_response_code_label'));
$select = $field->getSelect();
$select->addOption($addon->i18n('maintenance_http_response_code_503'), '503');
$select->addOption($addon->i18n('maintenance_http_response_code_403'), '403');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit');
$fragment->setVar('title', $addon->i18n('maintenance_settings'));
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');

?>
<!--
<script>
    $('#showform').toggle(
        $('#deakt-front').find("option[value='Aktivieren']").is(":checked")
    );


    $('#deakt-front').change(function() {
        if ($(this).val() === 'Aktivieren') {
            $('#showform').slideDown();
        } else {
            $('#showform').slideUp();
        }
    });

    if ($("#type option:selected").val() === 'PW') {
        $('#type-default').hide();
        $('#type-pw').show();
        $('#type-url').hide();
    }

    if ($("#type option:selected").val() === 'URL') {
        $('#type-default').hide();
        $('#type-pw').hide();
        $('#type-url').show();
    }

    $('#type').change(function() {
        if ($(this).val() === 'URL') {
            $('#type-default').hide();
            $('#type-pw').hide();
            $('#type-url').show();
        }
        if ($(this).val() === 'PW') {
            $('#type-default').hide();
            $('#type-pw').show();
            $('#type-url').hide();
        }
    });
</script>
-->
