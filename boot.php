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

// Stop if setup is active or maintenance is not activated
if (rex::isSetup() || rex::isBackend() || rex_addon::get('maintenance')->getConfig('frontend_aktiv') === 'Deaktivieren') return;

$addon = rex_addon::get('maintenance');

// Check for special requests or media files
$req = rex::isFrontend() ? $_SERVER['REQUEST_URI'] : '';
$media = rex_get('rex_media_file', 'string', '');
$media_unblocklist = rex_extension::registerPoint(new rex_extension_point('MAINTENANCE_MEDIA_UNBLOCK_LIST', []));

if (str_contains($req, 'sitemap.xml') && str_contains($req, 'secret=' . $addon->getConfig('secret')) || ($media !== '' && in_array($media, $media_unblocklist, true))) {
    return;
}

$secret = rex_session('secret', 'string', '');
$checksecret = rex_request('secret', 'string', '');

if (rex::isFrontend() && $addon->getConfig('secret') !== '') {
    if ($addon->getConfig('type') === 'PW' && $checksecret === $addon->getConfig('secret')) {
        rex_set_session('secret', $checksecret);
        $secret = $checksecret;
    }
}

// Handle frontend redirection or maintenance page
if (rex::isFrontend() && $secret === '') {
    $ips = array_filter(explode(", ", $addon->getConfig('ip') ?: ''));
    $domains = array_filter(explode(", ", $addon->getConfig('domains') ?: ''));

    $shouldRedirect = !in_array(rex_server('REMOTE_ADDR'), $ips, true) && !in_array(str_replace("www.", "", rex_server('SERVER_NAME', 'string', '')), $domains, true);
    if ($shouldRedirect) {
        $url = $addon->getConfig('redirect_frontend') ?: '';
        $responsecode = $addon->getConfig('responsecode');
        $mpage = new rex_fragment();
        $mpage = $mpage->parse($addon->getConfig('type') === 'PW' ? 'maintenance_page_pw_form.php' : 'maintenance_page.php');

        header('HTTP/1.1 ' . $responsecode);
        if ($url !== '') {
            rex_response::sendRedirect($url);
        } else {
            echo $mpage;
            exit;
        }
    }
}

// Backend maintenance handling
if ($addon->getConfig('backend_aktiv') === '1') {
    $session = rex_backend_login::createUser() !== null && rex::requireUser()->isAdmin();
    if (!$session) {
        $url = $addon->getConfig('redirect_backend') ?: '';
        $responsecode = $addon->getConfig('responsecode');
        $mpage = new rex_fragment();
        $mpage = $mpage->parse('maintenance_page_be.php');

        header('HTTP/1.1 ' . $responsecode);
        if ($url !== '') {
            rex_response::sendRedirect($url);
        } else {
            echo $mpage;
            exit;
        }
    }
}

// Output filter for backend icons
if ($addon->getConfig('backend_aktiv') === '1' || $addon->getConfig('frontend_aktiv') === 'Aktivieren') {
    rex_extension::register('OUTPUT_FILTER', function (rex_extension_point $ep) use ($addon) {
        $mode = $addon->getConfig('backend_aktiv') === '1' ? 'backend' : 'frontend';
        $search = '<i class="maintenance rex-icon fa-exclamation-triangle">';
        $replace = '<i title="Mode: Lock ' . ucfirst($mode) . '" class="rex-icon fa-exclamation-triangle aktivieren_' . $mode . '">';
        $ep->setSubject(str_replace($search, $replace, $ep->getSubject()));
    });
}

// Add assets
if (rex::isBackend()) {
    $assets = ['dist/bootstrap-tokenfield.js', 'dist/init_bootstrap-tokenfield.js', 'dist/css/bootstrap-tokenfield.css', 'css/maintenance.css'];
    foreach ($assets as $asset) {
        $function = str_ends_with($asset, '.css') ? 'addCssFile' : 'addJsFile';
        rex_view::$function($addon->getAssetsUrl($asset));
    }
}
