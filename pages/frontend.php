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
$isAdmin = rex::getUser()->isAdmin();

$form = rex_config_form::factory($addon->getName());

$form->addFieldset($addon->i18n('maintenance_general_title'));

// Activation/Deactivation of maintenance mode in frontend - visible for all users
$field = $form->addSelectField('block_frontend');
$field->setLabel($addon->i18n('maintenance_block_frontend_label'));
$select = $field->getSelect();
$select->addOption('Deaktiviert', 0, '<span><i class="fa-solid fa-circle-check text-success"></i> ' . rex_escape($addon->i18n('maintenance_block_frontend_false')) . ' <span class="badge badge-success">online</span></span>');
$select->addOption('Aktiviert', 1, '<span><i class="fa-solid fa-circle-xmark text-danger"></i> ' . rex_escape($addon->i18n('maintenance_block_frontend_true')) . ' <span class="badge badge-danger">offline</span></span>');
$select->setAttributes('class="form-control selectpicker" data-width="100%"');

if ($isAdmin) {
    // Authentication mode selection - admin only
    $field = $form->addSelectField('authentification_mode');
    $field->setLabel($addon->i18n('maintenance_authentification_mode_label'));
    $select = $field->getSelect();
    $select->addOption('URL', 'URL', '<span><i class="fa-solid fa-link"></i> ' . rex_escape($addon->i18n('maintenance_authentification_mode_url')) . '</span>');
    $select->addOption('Password', 'password', '<span><i class="fa-solid fa-key"></i> ' . rex_escape($addon->i18n('maintenance_authentification_mode_password')) . '</span>');
    $select->setAttributes('class="form-control selectpicker" data-width="100%"');

    // Block REDAXO users in frontend - admin only
    $field = $form->addSelectField('block_frontend_rex_user');
    $field->setLabel($addon->i18n('maintenance_block_frontend_rex_user_label'));
    $select = $field->getSelect();
    $select->addOption('Zugelassen', 0, '<span><i class="fa-solid fa-user-check text-success"></i> ' . rex_escape($addon->i18n('maintenance_block_frontend_rex_user_false')) . ' <span class="badge badge-success">erlaubt</span></span>');
    $select->addOption('Gesperrt', 1, '<span><i class="fa-solid fa-user-xmark text-danger"></i> ' . rex_escape($addon->i18n('maintenance_block_frontend_rex_user_rex_user')) . ' <span class="badge badge-danger">gesperrt</span></span>');
    $select->setAttributes('class="form-control selectpicker" data-width="100%"');

    // Password for maintenance mode bypass - admin only
    $field = $form->addTextField('maintenance_secret');
    $field->setLabel($addon->i18n('maintenance_secret_label'));
    $field->setNotice($addon->i18n('maintenance_secret_notice', bin2hex(random_bytes(16))));
    $field->setAttribute('type', 'password');

    // Redirect URL - admin only
    $field = $form->addTextField('redirect_frontend_to_url');
    $field->setLabel($addon->i18n('maintenance_redirect_frontend_to_url_label'));
    $field->setNotice($addon->i18n('maintenance_redirect_frontend_to_url_notice'));
    $field->setAttribute('type', 'url');

    // Response code - admin only
    $field = $form->addSelectField('http_response_code');
    $field->setLabel($addon->i18n('maintenance_http_response_code_label'));
    $select = $field->getSelect();
    $select->addOption('503', '503', '<span><i class="fa-solid fa-triangle-exclamation"></i> ' . rex_escape($addon->i18n('maintenance_http_response_code_503')) . '</span>');
    $select->addOption('403', '403', '<span><i class="fa-solid fa-ban"></i> ' . rex_escape($addon->i18n('maintenance_http_response_code_403')) . '</span>');
    $select->setAttributes('class="form-control selectpicker" data-width="100%"');
}

// Exceptions section - visible for all users
$form->addFieldset($addon->i18n('maintenance_allowed_access_title'));

// Allowed IP addresses
$field = $form->addTextField('allowed_ips');
$field->setLabel($addon->i18n('maintenance_allowed_ips_label'));
$field->setNotice($addon->i18n('maintenance_allowed_ips_notice', rex_server('REMOTE_ADDR', 'string', ''), rex_server('SERVER_ADDR', 'string', '')));
$field->setAttribute('class', 'form-control');
$field->setAttribute('data-maintenance', 'tokenfield');
$field->setAttribute('data-beautify', 'false');

// YRewrite domains selection if available
if (rex_addon::get('yrewrite')->isAvailable() && count(rex_yrewrite::getDomains()) > 1) {
    $field = $form->addSelectField('allowed_yrewrite_domains');
    $field->setAttribute('multiple', 'multiple');
    $field->setAttribute('class', 'form-control selectpicker');
    $field->setAttribute('data-live-search', 'true');
    $field->setLabel($addon->i18n('maintenance_allowed_yrewrite_domains_label'));
    $field->setNotice($addon->i18n('maintenance_allowed_yrewrite_domains_notice'));
    $select = $field->getSelect();
    foreach (rex_yrewrite::getDomains() as $key => $domain) {
        $select->addOption($key, $key, '<span><i class="fa-solid fa-globe"></i> ' . rex_escape($key) . '</span>');
    }
}

// Allowed domains
$field = $form->addTextField('allowed_domains');
$field->setLabel($addon->i18n('maintenance_allowed_domains_label'));
$field->setNotice($addon->i18n('maintenance_allowed_domains_notice'));
$field->setAttribute('class', 'form-control');
$field->setAttribute('data-maintenance', 'tokenfield');
$field->setAttribute('data-beautify', 'false');

if ($isAdmin) {
    // Maintenance announcement section - admin only
    $form->addFieldset($addon->i18n('maintenance_announcement_title'));

    // Announcement text
    $field = $form->addTextAreaField('announcement');
    $field->setLabel($addon->i18n('maintenance_announcement_label'));
    $field->setNotice($addon->i18n('maintenance_announcement_notice'));
    if ('' !== (string) rex_config::get('maintenance', 'editor')) {
        $field->setAttribute('class', '###maintenance-settings-editor###');
    }

    // Editor settings
    $field = $form->addTextField('editor');
    $field->setLabel($addon->i18n('maintenance_editor_label'));
    $field->setNotice($addon->i18n('maintenance_editor_notice'));

    // Announcement period
    $field = $form->addTextField('announcement_start_date');
    $field->setLabel($addon->i18n('maintenance_announcement_start_date_label'));
    $field->setNotice($addon->i18n('maintenance_announcement_start_date_notice', date('Y-m-d H:i:s')));
    $field->setAttribute('type', 'datetime-local');

    $field = $form->addTextField('announcement_end_date');
    $field->setLabel($addon->i18n('maintenance_announcement_end_date_label'));
    $field->setNotice($addon->i18n('maintenance_announcement_end_date_notice', date('Y-m-d H:i:s')));
    $field->setAttribute('type', 'datetime-local');
}

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit');
$fragment->setVar('title', $addon->i18n('maintenance_settings_frontend_title'));
$fragment->setVar('body', $form->get(), false);
?>

<div class="row">
    <div class="col-lg-8">
        <?= $fragment->parse('core/page/section.php') ?>
    </div>
    <?php if ($isAdmin): ?>
    <div class="col-lg-4">
        <?php
        /* Preview of maintenance mode */
        $preview = '<a target="_blank" href="' . rex_url::backendPage('maintenance/preview') . '" class="btn btn-primary"><i class="fa-solid fa-eye"></i> ' . rex_i18n::msg('maintenance_preview') . '</a>';

        $fragment = new rex_fragment();
        $fragment->setVar('class', 'info', false);
        $fragment->setVar('title', '<i class="fa-solid fa-eye"></i> ' . rex_i18n::msg('maintenance_preview_title'), false);
        $fragment->setVar('body', $preview, false);
        echo $fragment->parse('core/page/section.php');

        /* Copy maintenance mode URL */
        $copy = '<ul class="list-group">';
        $url = '' . rex::getServer() . '?maintenance_secret=' . rex_config::get('maintenance', 'maintenance_secret');
        $copy .= '<li class="list-group-item"><label for="maintenance-mode-url"><i class="fa-solid fa-gear"></i> REDAXO config.yml</label>
        <div class="hidden" id="maintenance-mode-url"><code>' . $url . '</code></div>';
        $copy .= '
        <clipboard-copy for="maintenance-mode-url" class="input-group">
          <input type="text" value="' . $url . '" readonly class="form-control">
          <span class="input-group-addon"><i class="fa-regular fa-copy"></i></span>
        </clipboad-copy></li>';

        // Also output for all YRewrite domains
        if (rex_addon::get('yrewrite')->isAvailable() && count(rex_yrewrite::getDomains()) > 1) {
            foreach (rex_yrewrite::getDomains() as $key => $domain) {
                if ('default' == $key) {
                    continue;
                }
                $url = $domain->getUrl() . '?maintenance_secret=' . rex_config::get('maintenance', 'maintenance_secret');
                $copy .= '<li class="list-group-item"><label for="maintenance-mode-url-' . $key . '"><i class="fa-solid fa-globe"></i> YRewrite ' . rex_escape($key) . '</label>';
                $copy .= '<div class="hidden" id="maintenance-mode-url-' . $key . '"><code>' . $url . '</code></div>';
                $copy .= '
                <clipboard-copy for="maintenance-mode-url-' . $key . '" class="input-group">
                  <input type="text" value="' . $url . '" readonly class="form-control">
                  <span class="input-group-addon"><i class="fa-regular fa-copy"></i></span>
                </clipboad-copy></li>';
            }
        }

        $copy .= '</ul>';

        $fragment = new rex_fragment();
        $fragment->setVar('class', 'info', false);
        $fragment->setVar('title', '<i class="fa-regular fa-copy"></i> ' . rex_i18n::msg('maintenance_copy_url_title'), false);
        $fragment->setVar('body', $copy, false);
        echo $fragment->parse('core/page/section.php');
        ?>
    </div>
    <?php endif; ?>
</div>
