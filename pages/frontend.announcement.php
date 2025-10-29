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

// Wartungsfenster-Ankündigung
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
$fragment->setVar('class', 'edit');
$fragment->setVar('title', $addon->i18n('maintenance_announcement_title'));
$fragment->setVar('body', $form->get(), false);
?>

<div class="row">
    <div class="col-lg-8">
        <?= $fragment->parse('core/page/section.php') ?>
    </div>
    <div class="col-lg-4">
        <?php
        /* Info-Box zur Verwendung */
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

        /* Sidebar-Panel einbinden */
        include __DIR__ . '/frontend.sidebar.php';
        ?>
    </div>
</div>
