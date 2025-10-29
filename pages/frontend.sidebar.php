<?php

/**
 * Sidebar-Panel für Frontend-Seiten des Maintenance AddOns
 * Zeigt Vorschau, Bypass-URLs und Quick-Links.
 */

$addon = rex_addon::get('maintenance');
$sidebarContent = '';

/* Vorschau des Wartungsmodus */
$preview = '<a target="_blank" href="' . rex_url::backendPage('maintenance/preview') . '" class="btn btn-primary btn-block">';
$preview .= '<i class="rex-icon fa-eye"></i> ' . rex_i18n::msg('maintenance_preview') . '</a>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'info', false);
$fragment->setVar('title', rex_i18n::msg('maintenance_preview_title'), false);
$fragment->setVar('body', $preview, false);
$sidebarContent .= $fragment->parse('core/page/section.php');

/* Kopieren der URL für den Wartungsmodus */
$copy = '<ul class="list-group">';

// Prüfen, ob YRewrite verfügbar ist
$yrewriteAvailable = rex_addon::exists('yrewrite') && rex_addon::get('yrewrite')->isAvailable();
$allDomainsLocked = rex_config::get('maintenance', 'all_domains_locked', false);
$domainStatus = rex_config::get('maintenance', 'domain_status', []);

// Standard-Domain immer anzeigen, wenn Frontend-Wartung aktiv ist oder alle Domains gesperrt sind
if (rex_config::get('maintenance', 'block_frontend') || $allDomainsLocked) {
    $url = '' . rex::getServer() . '?maintenance_secret=' . rex_config::get('maintenance', 'maintenance_secret');
    $copy .= '<li class="list-group-item">';
    $copy .= '<label for="maintenance-mode-url">' . $addon->i18n('maintenance_bypass_url_default') . '</label>';
    $copy .= '
    <clipboard-copy for="maintenance-mode-url" class="input-group">
      <input id="maintenance-mode-url" type="text" value="' . $url . '" readonly class="form-control">
      <span class="input-group-addon"><i class="rex-icon fa-clone"></i></span>
    </clipboard-copy></li>';
}

// YRewrite-Domains nur anzeigen, wenn sie gesperrt sind
if ($yrewriteAvailable && count(rex_yrewrite::getDomains()) > 1) {
    foreach (rex_yrewrite::getDomains() as $key => $domain) {
        if ('default' == $key) {
            continue;
        }

        // Domain nur anzeigen, wenn:
        // - Alle Domains gesperrt sind ODER
        // - Diese spezifische Domain gesperrt ist
        $isDomainLocked = $allDomainsLocked || (isset($domainStatus[$key]) && 1 == $domainStatus[$key]);

        if ($isDomainLocked) {
            $url = $domain->getUrl() . '?maintenance_secret=' . rex_config::get('maintenance', 'maintenance_secret');
            $copy .= '<li class="list-group-item">';
            $copy .= '<label for="maintenance-mode-url-' . $key . '">YRewrite: ' . htmlspecialchars($key) . '</label>';
            $copy .= '
            <clipboard-copy for="maintenance-mode-url-' . $key . '" class="input-group">
              <input id="maintenance-mode-url-' . $key . '" type="text" value="' . $url . '" readonly class="form-control">
              <span class="input-group-addon"><i class="rex-icon fa-clone"></i></span>
            </clipboard-copy></li>';
        }
    }
}

$copy .= '</ul>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'info', false);
$fragment->setVar('title', rex_i18n::msg('maintenance_copy_url_title'), false);
$fragment->setVar('body', $copy, false);
$sidebarContent .= $fragment->parse('core/page/section.php');

/* Quick Links */
$quickLinks = '<div class="btn-group-vertical btn-block">';

// Link zur Hauptseite (Allgemeine Einstellungen)
$currentPage = rex_be_controller::getCurrentPage();
if ('maintenance/frontend/index' !== $currentPage && 'maintenance/frontend' !== $currentPage) {
    $quickLinks .= '<a href="' . rex_url::backendPage('maintenance/frontend') . '" class="btn btn-default">';
    $quickLinks .= '<i class="rex-icon fa-sliders"></i> ' . $addon->i18n('maintenance_frontend_general_title') . '</a>';
}

if (rex::getUser()->isAdmin()) {
    if ('maintenance/frontend/advanced' !== $currentPage) {
        $quickLinks .= '<a href="' . rex_url::backendPage('maintenance/frontend/advanced') . '" class="btn btn-default">';
        $quickLinks .= '<i class="rex-icon fa-cog"></i> ' . $addon->i18n('maintenance_settings_title') . '</a>';
    }

    if ('maintenance/frontend/announcement' !== $currentPage) {
        $quickLinks .= '<a href="' . rex_url::backendPage('maintenance/frontend/announcement') . '" class="btn btn-default">';
        $quickLinks .= '<i class="rex-icon fa-bullhorn"></i> ' . $addon->i18n('maintenance_announcement_settings') . '</a>';
    }
}

if (rex_addon::exists('yrewrite') && rex_addon::get('yrewrite')->isAvailable() && $currentPage !== 'maintenance/domains') {
    $quickLinks .= '<a href="' . rex_url::backendPage('maintenance/domains') . '" class="btn btn-default">';
    $quickLinks .= '<i class="rex-icon fa-sitemap"></i> ' . $addon->i18n('maintenance_domain_settings') . '</a>';
}

$quickLinks .= '</div>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'info', false);
$fragment->setVar('title', $addon->i18n('maintenance_quick_links'), false);
$fragment->setVar('body', $quickLinks, false);
$sidebarContent .= $fragment->parse('core/page/section.php');

echo $sidebarContent;
