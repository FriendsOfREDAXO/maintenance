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

// ===== ZEITGESTEUERTE WARTUNG =====
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

// ===== WARTUNGSANKÜNDIGUNG =====
$form->addFieldset($addon->i18n('maintenance_announcement_title'));

// Benachrichtigungstext
$field = $form->addTextAreaField('announcement');
$field->setLabel($addon->i18n('maintenance_announcement_label'));
$field->setNotice($addon->i18n('maintenance_announcement_notice'));
if ('' !== (string) rex_config::get('maintenance', 'editor')) {
    $field->setAttribute('class', '###maintenance-settings-editor###');
}

// Start- und Endzeitpunkt der Wartungsankündigung
$field = $form->addTextField('announcement_start_date');
$field->setLabel($addon->i18n('maintenance_announcement_start_date_label'));
$field->setNotice(rex_i18n::rawMsg('maintenance_announcement_start_date_notice', date('Y-m-d H:i:s')));
$field->setAttribute('type', 'datetime-local');

$field = $form->addTextField('announcement_end_date');
$field->setLabel($addon->i18n('maintenance_announcement_end_date_label'));
$field->setNotice(rex_i18n::rawMsg('maintenance_announcement_end_date_notice', date('Y-m-d H:i:s')));
$field->setAttribute('type', 'datetime-local');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('maintenance_planning_page_title'), false);
$fragment->setVar('body', $form->get(), false);
?>

<div class="row">
    <div class="col-lg-8">
        <?= $fragment->parse('core/page/section.php') ?>
    </div>
    <div class="col-lg-4">
        <?php
        // Info-Box zur Verwendung der Ankündigung
        $codeExample = '<?php
use FriendsOfREDAXO\Maintenance\Maintenance;
Maintenance::showAnnouncement();
?>';

        $info = '<div class="alert alert-info">';
        $info .= '<h4><i class="rex-icon fa-info-circle"></i> ' . $addon->i18n('maintenance_announcement_info_title') . '</h4>';
        $info .= '<p>' . $addon->i18n('maintenance_announcement_info_text') . '</p>';
        $info .= '<p><strong>' . $addon->i18n('maintenance_announcement_usage') . ':</strong></p>';
        $info .= '<div style="position: relative;">';
        $info .= '<pre style="margin-bottom: 0;"><code>' . htmlspecialchars($codeExample) . '</code></pre>';
        $info .= '<button class="btn btn-xs btn-default" style="position: absolute; top: 5px; right: 5px;" onclick="navigator.clipboard.writeText(' . htmlspecialchars(json_encode($codeExample), ENT_QUOTES) . '); this.innerHTML=\'<i class=\\\'rex-icon fa-check\\\'></i> ' . $addon->i18n('maintenance_copied') . '\'; setTimeout(() => this.innerHTML=\'<i class=\\\'rex-icon fa-copy\\\'></i> ' . $addon->i18n('maintenance_copy') . '\', 2000);"><i class="rex-icon fa-copy"></i> ' . $addon->i18n('maintenance_copy') . '</button>';
        $info .= '</div>';
        $info .= '</div>';

        echo $info;

        // Sidebar
        $sidebar = new rex_fragment();
        $sidebar->setVar('title', $addon->i18n('maintenance_quick_links'));

        $content = '<ul class="list-group">';
        $content .= '<li class="list-group-item"><a href="' . rex_url::backendPage('maintenance/frontend') . '"><i class="rex-icon fa-sliders"></i> ' . $addon->i18n('maintenance_frontend_general_title') . '</a></li>';
        $content .= '<li class="list-group-item"><a href="' . rex_url::backendPage('maintenance/frontend/advanced') . '"><i class="rex-icon fa-cog"></i> ' . $addon->i18n('maintenance_advanced_settings') . '</a></li>';

        if (rex_addon::exists('yrewrite') && rex_addon::get('yrewrite')->isAvailable()) {
            $content .= '<li class="list-group-item"><a href="' . rex_url::backendPage('maintenance/domains') . '"><i class="rex-icon fa-sitemap"></i> ' . $addon->i18n('maintenance_domain_settings') . '</a></li>';
        }

        if (rex_addon::get('cronjob')->isAvailable()) {
            $content .= '<li class="list-group-item"><a href="' . rex_url::backendPage('cronjob/cronjobs') . '"><i class="rex-icon fa-clock-o"></i> Cronjob-Verwaltung</a></li>';
        }

        $content .= '</ul>';

        $sidebar->setVar('body', $content, false);
        echo $sidebar->parse('core/page/section.php');
        ?>
    </div>
</div>

