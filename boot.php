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

// Wenn sich REDAXO im Setup-Modus befindet, darf der Wartungsmodus nicht ausgeführt werden
if (rex::isSetup()) {
    return;
}

$addon = rex_addon::get('maintenance');

if (rex::isFrontend() && $addon->getConfig('block_frontend')) {

    $req = $_SERVER['REQUEST_URI'];
    if (str_contains($req, 'sitemap.xml') === true && str_contains($req, 'secret='.$addon->getConfig('secret'))) {
        return;
    }

    $media = rex_get('rex_media_file', 'string', '');
    $media_unblock = [];
    $media_unblocklist = rex_extension::registerPoint(new rex_extension_point('MAINTENANCE_MEDIA_UNBLOCK_LIST', $media_unblock));

    if ($media !== '' && count($media_unblocklist) > 0) {
        if (in_array($media, $media_unblocklist)) {
            return;
        }
    }
}

$secret = '';
$responsecode = $addon->getConfig('responsecode');

if (rex::isFrontend() && $addon->getConfig('block_frontend') and $addon->getConfig('secret') !== '') {

    rex_login::startSession();
    $secret = rex_session('secret', 'string', '');

    // GET-Parameter abfragen
    $checksecret = rex_request('secret', 'string', false);

    //Überprüfen ob eingegebenes PW stimmt, wenn ja Session beschreiben, ansosnten unten PW-Fragment anzeigen
    if ($addon->getConfig('type') === 'PW' && $checksecret === $addon->getConfig('secret')) {
        // speichert den Code in der Session
        rex_set_session('secret', $checksecret);
        $secret = rex_session('secret');
    }

    // speichert den Code in der Session
    if ($checksecret !== 0) {
        $code = $addon->getConfig('secret');
        if ($code === $checksecret) {
            rex_set_session('secret', $checksecret);
            $secret = rex_session('secret');
        }
    }
}

// Ausgabe abbrechen, wenn der übermittelte Code nicht stimmt.
if (rex::isFrontend() and $addon->getConfig('block_frontend') !== 'Deaktivieren' and $secret === '') {
    $ips = [];
    $domains = [];
    $iplist = $addon->getConfig('ip');
    if (is_string($iplist)) {
        $ips = explode(", ", $iplist);
    }

    $domainlist = $addon->getConfig('domains');
    if (is_string($domainlist)) {
        $domains = explode(", ", $domainlist);
    }
    if ($addon->getConfig('block_frontend') === 'Aktivieren') {
        $session = rex_backend_login::hasSession();
        $redirect = 'inaktiv';
        $admin = false;
        if (rex_backend_login::createUser() !== null) {
            $admin = rex::requireUser()->isAdmin();
        }
        if ($addon->getConfig('blockSession') === 'Inaktiv' && in_array(rex_server('REMOTE_ADDR'), $ips, true)) {
            $redirect = 'inaktiv';
        }
        if ($addon->getConfig('blockSession') === "Redakteure" && $admin === false && !in_array(rex_server('REMOTE_ADDR'), $ips, true)) {
            $redirect = 'aktiv';
        }
        if ($addon->getConfig('blockSession') === "Redakteure" && $admin === true) {
            $redirect = 'inaktiv';
        } else {
            if (!$session) {
                $redirect = "aktiv";
            }


            $current_domain = '';
            if (is_string(rex_server('SERVER_NAME'))) {
                $current_domain =  str_replace("www.", "", rex_server('SERVER_NAME'));
            } elseif (is_string(rex_server('HTTP_HOST'))) {
                $current_domain =  str_replace("www.", "", rex_server('HTTP_HOST'));
            }
            if ($current_domain === '') {
                throw new LogicException('Maintenance-AddOn: No Domain found, SERVER_NAME OR HTTP_HOST not defined');
            }
            if (in_array($current_domain, $domains, true)) {
                $redirect = 'inaktiv';
            }

            if (in_array(rex_server('REMOTE_ADDR'), $ips, true)) {
                $redirect = "inaktiv";
            }
        }

        if ($redirect === 'aktiv') {
            $url = $addon->getConfig('redirect_frontend');
            $mpage = new rex_fragment();
            if ($addon->getConfig('type') === 'PW') {
                $mpage = $mpage->parse('maintenance_page_pw_form.php');
            } else {
                $mpage = $mpage->parse('maintenance_page.php');
            }

            if (is_string($url) && $url !== '') {
                rex_response::setStatus(rex_response::HTTP_MOVED_TEMPORARILY);
                rex_response::sendRedirect($url);
            } else {
                header('HTTP/1.1 ' . $responsecode);
                echo $mpage;
                die();
            }
        }
    }
}

if (rex::isBackend()) {
    $user = rex::getUser();
    if ($user !== null) {
        if ($addon->getConfig('block_backend') === '1') {
            $session = false;
            if (rex_backend_login::createUser() !== null) {
                $session = rex::requireUser()->isAdmin();
            }
            $redirect = '';
            if ($session === false) {
                $redirect = true;
            }
            if ($session === true || rex::getImpersonator() !== null) {
                $redirect = false;
            }
            if ($redirect === true) {
                $url = $addon->getConfig('redirect_backend_to_url');
                $mpage = new rex_fragment();
                $mpage = $mpage->parse('maintenance_page_be.php');

                if (is_string($url) && $url !== '') {
                    rex_response::setStatus(rex_response::HTTP_MOVED_TEMPORARILY);
                    rex_response::sendRedirect($url);
                } else {

                    header('HTTP/1.1 ' . $responsecode);
                    echo $mpage;
                    die();
                }
            }
        }
    }
    if ($addon->getConfig('block_backend') === true) {
        rex_extension::register('OUTPUT_FILTER', function (rex_extension_point $magic) {
            $header = '<i class="maintenance rex-icon fa-exclamation-triangle">';
            $replace = '<i title="Mode: Lock Backend" class="rex-icon fa-exclamation-triangle">';
            $subject = $magic->getSubject();
            if (is_string($subject)) {
                $out = str_replace($header, $replace, $subject);
                $magic->setSubject($out);
            }
        });
    }
    if ($addon->getConfig('block_frontend') === true) {
        rex_extension::register('OUTPUT_FILTER', function (rex_extension_point $ep) {
            $suchmuster = '<i class="maintenance rex-icon fa-exclamation-triangle">';
            $ersetzen = '<i title="Mode: Lock Frontend" class="rex-icon fa-exclamation-triangle">';
            $subject = $ep->getSubject();
            if (is_string($subject)) {
                $out = str_replace($suchmuster, $ersetzen, $subject);
                $ep->setSubject($out);
            }
        });
    }
    rex_view::addJsFile($addon->getAssetsUrl('dist/bootstrap-tokenfield.js'));
    rex_view::addJsFile($addon->getAssetsUrl('dist/init_bootstrap-tokenfield.js'));
    rex_view::addCssFile($addon->getAssetsUrl('dist/css/bootstrap-tokenfield.css'));
    rex_view::addCssFile($addon->getAssetsUrl('css/maintenance.css'));
}
