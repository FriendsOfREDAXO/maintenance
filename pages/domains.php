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

// Wenn YRewrite nicht installiert oder verfügbar ist, Hinweis anzeigen
if (!rex_addon::exists('yrewrite') || !rex_addon::get('yrewrite')->isAvailable()) {
    echo rex_view::info($addon->i18n('maintenance_yrewrite_not_available'));
    return;
}

// Domains aus YRewrite holen
$domains = rex_yrewrite::getDomains();

// Aktuelle Einstellungen laden
$domainStatus = (array) $addon->getConfig('domain_status', []);
$allDomainsLocked = (bool) $addon->getConfig('all_domains_locked', false);

// Formular abgesendet?
if (rex_post('save', 'bool')) {
    // Globale Option für alle Domains speichern
    $allDomainsLocked = rex_post('all_domains_locked', 'bool', false);
    $addon->setConfig('all_domains_locked', $allDomainsLocked);

    // Nur wenn nicht alle Domains gesperrt sind, individuelle Einstellungen speichern
    if (!$allDomainsLocked) {
        // Einstellungen speichern
        $domainStatus = [];
        foreach ($domains as $domain) {
            $name = $domain->getName();
            if ('default' !== $name) {
                $domainStatus[$name] = rex_post('domain_' . md5($name), 'bool', false);
            }
        }

        // Konfiguration speichern
        $addon->setConfig('domain_status', $domainStatus);
    }

    // Erfolgsmeldung
    echo rex_view::success($addon->i18n('maintenance_settings_saved'));
}

// Tabelle mit Domains erstellen
$content = '<form action="' . rex_url::currentBackendPage() . '" method="post">';

// Option für alle Domains
$content .= '<div class="form-group">';
$content .= '<label class="control-label">' . $addon->i18n('maintenance_lock_all_domains') . '</label>';
$content .= '<div class="rex-select-style">';
$content .= '<select class="form-control" name="all_domains_locked" id="all-domains-locked">';
$content .= '<option value="0"' . (!$allDomainsLocked ? ' selected' : '') . '>' . $addon->i18n('maintenance_no') . '</option>';
$content .= '<option value="1"' . ($allDomainsLocked ? ' selected' : '') . '>' . $addon->i18n('maintenance_yes') . '</option>';
$content .= '</select>';
$content .= '</div>';
$content .= '</div>';

// Individuelle Domain-Einstellungen
$content .= '<div id="individual-domains" ' . ($allDomainsLocked ? 'style="display:none;"' : '') . '>';
$content .= '<table class="table table-striped table-hover">';
$content .= '<thead><tr>';
$content .= '<th>' . $addon->i18n('maintenance_domain') . '</th>';
$content .= '<th>' . $addon->i18n('maintenance_maintenance_active') . '</th>';
$content .= '</tr></thead>';
$content .= '<tbody>';

foreach ($domains as $domain) {
    $name = $domain->getName();
    if ('default' !== $name) {
        $content .= '<tr>';
        $content .= '<td><strong>' . htmlspecialchars($name) . '</strong><br><small class="text-muted">' . htmlspecialchars($domain->getUrl()) . '</small></td>';
        $content .= '<td>';
        $content .= '<div class="rex-select-style">';
        $content .= '<select class="form-control" name="domain_' . md5($name) . '">';
        $isActive = $domainStatus[$name] ?? false;
        $content .= '<option value="0"' . (!$isActive ? ' selected' : '') . '>' . $addon->i18n('maintenance_domain_inactive') . '</option>';
        $content .= '<option value="1"' . ($isActive ? ' selected' : '') . '>' . $addon->i18n('maintenance_domain_active') . '</option>';
        $content .= '</select>';
        $content .= '</div>';
        $content .= '</td>';
        $content .= '</tr>';
    }
}

$content .= '</tbody></table>';
$content .= '</div>';

$content .= '<div class="form-group">';
$content .= '<button class="btn btn-save rex-primary-action" type="submit" name="save" value="1"><i class="rex-icon rex-icon-save"></i> ' . $addon->i18n('maintenance_save') . '</button>';
$content .= '</div>';
$content .= '</form>';

// Hinweis zur Domain-Konfiguration
$notice = '<div class="alert alert-info">';
$notice .= '<p>' . $addon->i18n('maintenance_domains_notice') . '</p>';
$notice .= '</div>';

// Fragment erstellen und ausgeben
$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('maintenance_domains_management'), false);
$fragment->setVar('body', $notice . $content, false);
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
    $('#all-domains-locked').on('change', function() {
        if ($(this).val() == '1') {
            $('#individual-domains').slideUp();
        } else {
            $('#individual-domains').slideDown();
        }
    });
});
</script>
