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

// Überschrift für den Wartungsmodus
$field = $form->addTextField('maintenance_backend_headline');
$field->setLabel($addon->i18n('maintenance_backend_headline_label'));
$field->setNotice($addon->i18n('maintenance_backend_headline_notice'));

// Automatische Aktualisierung der Seite
$field = $form->addInputField('number', 'maintenance_backend_update_interval');
$field->setLabel($addon->i18n('maintenance_update_interval_field_label'));
$field->setNotice($addon->i18n('maintenance_update_interval_field_notice'));
$field->setAttribute('class', 'form-control');

$field = $form->addSelectField('block_backend');
$field->setLabel($addon->i18n('maintenance_block_backend_label'));
$select = $field->getSelect();
$select->addOption($addon->i18n('maintenance_block_backend_true'), 1);
$select->addOption($addon->i18n('maintenance_block_backend_false'), 0);

$field = $form->addTextField('redirect_backend_to_url');
$field->setLabel($addon->i18n('maintenance_redirect_backend_to_url_label'));
$field->setAttribute('type', 'url');
$field->setNotice(rex_i18n::msg('maintenance_redirect_backend_to_url_notice'));
$field->getValidator()->add('url', $addon->i18n('error_invalid_url'));

$content = $form->get();

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit');
$fragment->setVar('title', $addon->i18n('maintenance_settings_backend_title'));
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
