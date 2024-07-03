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
$checkLink = new MaintenanceUtil();

$form = rex_config_form::factory($addon->getName());

$field = $form->addTextField('redirect_backend_to_url');
$field->setLabel($addon->i18n('maintenance_redirect_backend_to_url_label'));
$field->setAttribute('type', 'url');
$field->setNotice('https://example.tld');
$field->getValidator()->add('url', $addon->i18n('error_invalid_url'));

$field = $form->addCheckboxField('block_backend');
$field->setLabel($addon->i18n('maintenance_block_backend_label'));
$field->addOption($addon->i18n('yes'), '1');

$content = $form->get();

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit');
$fragment->setVar('title', $addon->i18n('maintenance_settings_backend_title'));
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');
