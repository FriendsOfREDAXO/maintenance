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

$form = rex_config_form::factory($addon->getName());

$form->addFieldset($addon->i18n('maintenance_general_title'));

// Aktivierung/Deaktivierung des Wartungsmodus im Frontend
$field = $form->addSelectField('block_frontend');
$field->setLabel($addon->i18n('maintenance_block_frontend_label'));
$select = $field->getSelect();
$select->addOption($addon->i18n('maintenance_block_frontend_false'), 0);
$select->addOption($addon->i18n('maintenance_block_frontend_true'), 1);

// Überschrift für den Wartungsmodus
$field = $form->addTextField('maintenance_frontend_headline');
$field->setLabel($addon->i18n('maintenance_frontend_headline_label'));
$field->setNotice($addon->i18n('maintenance_frontend_headline_notice'));

// Wartungstext Englisch
$field = $form->addTextField('maintenance_text_en');
$field->setLabel($addon->i18n('maintenance_text_en_label'));
$field->setNotice($addon->i18n('maintenance_text_en_notice'));

// Wartungstext Deutsch
$field = $form->addTextField('maintenance_text_de');
$field->setLabel($addon->i18n('maintenance_text_de_label'));
$field->setNotice($addon->i18n('maintenance_text_de_notice'));

// Automatische Aktualisierung der Seite
$field = $form->addInputField('number', 'maintenance_frontend_update_interval');
$field->setLabel($addon->i18n('maintenance_update_interval_field_label'));
$field->setNotice($addon->i18n('maintenance_update_interval_field_notice'));
$field->setAttribute('class', 'form-control');

// Passwort zum Umgehen des Wartungsmodus
$field = $form->addTextField('maintenance_secret');
$field->setLabel($addon->i18n('maintenance_secret_label'));
$field->setNotice($addon->i18n('maintenance_secret_notice', bin2hex(random_bytes(16))));
$field->setAttribute('type', 'password');

// Umgehung der Wartung durch GET-Parameter (URL) oder Passwort
$field = $form->addSelectField('authentication_mode');
$field->setLabel($addon->i18n('maintenance_authentication_mode_label'));
$select = $field->getSelect();
$select->addOption($addon->i18n('maintenance_authentication_mode_url'), 'URL');
$select->addOption($addon->i18n('maintenance_authentication_mode_password'), 'password');

// Blockiere auch für angemeldete REDAXO-Benutzer das Frontend
$field = $form->addSelectField('block_frontend_rex_user');
$field->setLabel($addon->i18n('maintenance_block_frontend_rex_user_label'));
$field->setNotice($addon->i18n('maintenance_block_frontend_rex_user_notice'));
$select = $field->getSelect();
$select->addOption($addon->i18n('maintenance_block_frontend_rex_user_false'), 0);
$select->addOption($addon->i18n('maintenance_block_frontend_rex_user_rex_user'), 1);

// Ziel der Umleitung
$field = $form->addTextField('redirect_frontend_to_url');
$field->setLabel($addon->i18n('maintenance_redirect_frontend_to_url_label'));
$field->setNotice($addon->i18n('maintenance_redirect_frontend_to_url_notice'));
$field->setAttribute('type', 'url');

// Scheduled Maintenance
$form->addFieldset($addon->i18n('maintenance_scheduled_title'));

// Info-Text für geplante Wartung mit Cronjob-Hinweis
$cronjobInstalled = false;
if (rex_addon::get('cronjob')->isAvailable()) {
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT id FROM ' . rex::getTable('cronjob') . ' WHERE type = :type LIMIT 1', ['type' => 'rex_cronjob_scheduled_maintenance']);
    $cronjobInstalled = $sql->getRows() > 0;
}

$infoHtml = '<div class="alert alert-info">';
$infoHtml .= '<p>' . $addon->i18n('maintenance_scheduled_info') . '</p>';
if (!$cronjobInstalled) {
    $infoHtml .= '<p><strong><i class="rex-icon fa-exclamation-triangle"></i> ' . $addon->i18n('maintenance_scheduled_cronjob_missing') . '</strong><br>';
    $infoHtml .= '<a href="' . rex_url::backendPage('cronjob/cronjobs', ['func' => 'add']) . '" class="btn btn-primary btn-xs">';
    $infoHtml .= '<i class="rex-icon fa-plus"></i> ' . $addon->i18n('maintenance_scheduled_cronjob_create') . '</a></p>';
} else {
    $infoHtml .= '<p class="text-success"><i class="rex-icon fa-check"></i> ' . $addon->i18n('maintenance_scheduled_cronjob_active') . '</p>';
}
$infoHtml .= '</div>';
$field = $form->addRawField($infoHtml);

// Geplanter Start
$field = $form->addTextField('scheduled_start');
$field->setLabel($addon->i18n('maintenance_scheduled_start_label'));
$field->setNotice($addon->i18n('maintenance_scheduled_start_notice') . '<br><small>' . $addon->i18n('maintenance_scheduled_example') . '</small>');
$field->setAttribute('placeholder', '2025-12-31 02:00:00');

// Geplantes Ende
$field = $form->addTextField('scheduled_end');
$field->setLabel($addon->i18n('maintenance_scheduled_end_label'));
$field->setNotice($addon->i18n('maintenance_scheduled_end_notice') . '<br><small>' . $addon->i18n('maintenance_scheduled_example') . '</small>');
$field->setAttribute('placeholder', '2025-12-31 06:00:00');

// Aktuellen Status anzeigen
$scheduledStart = (string) $addon->getConfig('scheduled_start', '');
$scheduledEnd = (string) $addon->getConfig('scheduled_end', '');
if ('' !== $scheduledStart || '' !== $scheduledEnd) {
    $statusHtml = '<div class="alert alert-info">';
    $statusHtml .= '<strong>' . $addon->i18n('maintenance_scheduled_active') . '</strong><br>';
    if ('' !== $scheduledStart) {
        $statusHtml .= $addon->i18n('maintenance_scheduled_starts_at') . ': <code>' . rex_escape($scheduledStart) . '</code><br>';
    }
    if ('' !== $scheduledEnd) {
        $statusHtml .= $addon->i18n('maintenance_scheduled_ends_at') . ': <code>' . rex_escape($scheduledEnd) . '</code>';
    }
    $statusHtml .= '</div>';
    $field = $form->addRawField($statusHtml);
}

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('maintenance_settings_frontend_title'), false);
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
