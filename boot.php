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
// stop if Setup is active
if (rex::isSetup()) return;
$addon = rex_addon::get('maintenance');
$secret = '';
$responsecode = $addon->getConfig('responsecode');

if (rex::isFrontend() and $addon->getConfig('frontend_aktiv') != 'Deaktivieren' and $addon->getConfig('secret') != '') {
    rex_login::startSession();
    if (rex_session('secret') != '') {
        $secret = rex_session('secret');
    }

    // GET-Parameter abfragen
    $checksecret = rex_request('secret', 'string', 0);

    //Überprüfen ob eingegebenes PW stimmt, wenn ja Session beschreiben, ansosnten unten PW-Fragment anzeigen
    if ($addon->getConfig('type') == 'PW' && $checksecret === $this->getConfig('secret')) {
        // speichert den Code in der Session
        rex_set_session('secret', $checksecret);
        $secret = rex_session('secret');
    }

    // speichert den Code in der Session
    if ($checksecret) {
        $code = $this->getConfig('secret');
        if ($code === $checksecret) {
            rex_set_session('secret', $checksecret);
            $secret = rex_session('secret');
        }
    }
}
// Ausgabe abbrechen, wenn der übermittelte Code nicht stimmt.
if (rex::isFrontend() and $addon->getConfig('frontend_aktiv') != 'Deaktivieren' and $secret == '') {
    $ips = [];
    $domains = [];
    $ips = explode(", ", $this->getConfig('ip'));
    $domains = explode(", ", $this->getConfig('domains'));

    if ($addon->getConfig('frontend_aktiv') == 'Aktivieren') {
        $session = rex_backend_login::hasSession();
        $redirect = 'inaktiv';
        if (rex_backend_login::createUser()) {
            $admin = rex::getUser()->isAdmin();
        }
        if ($addon->getConfig('blockSession') == 'Inaktiv') {
            $redirect = 'inaktiv';
        }
        if ($addon->getConfig('blockSession') == 'Inaktiv' && in_array(rex_server('REMOTE_ADDR'), $ips)) {
            $redirect = 'inaktiv';
        }
        if ($addon->getConfig('blockSession') == "Redakteure" && $admin == false && !in_array(rex_server('REMOTE_ADDR'), $ips)) {
            $redirect = 'aktiv';
        }
        if ($addon->getConfig('blockSession') == "Redakteure" && $admin == true) {
            $redirect = 'inaktiv';
        } else {
            if (!$session) {
                $redirect = "aktiv";
            }


            $current_domain = '';
            if (rex_server('SERVER_NAME') != '') {
                $current_domain =  str_replace("www.", "", rex_server('SERVER_NAME'));
            } elseif (rex_server('HTTP_HOST') != '') {
                $current_domain =  str_replace("www.", "", rex_server('HTTP_HOST'));
            }
            if ($current_domain == '')
            {
                throw new LogicException('Maintenance-AddOn: No Domain found, SERVER_NAME OR HTTP_HOST not defined');   
            }
            if (in_array($current_domain, $domains)) {
                $redirect = 'inaktiv';
            }

            if (in_array(rex_server('REMOTE_ADDR'), $ips)) {
                $redirect = "inaktiv";
            }
        }

        if ($redirect == 'aktiv') {
            $url = $this->getConfig('redirect_frontend');
            $mpage = new rex_fragment();
            if ($addon->getConfig('type') == 'PW') {
                $mpage = $mpage->parse('maintenance_page_pw_form.php');
            } else {
                $mpage = $mpage->parse('maintenance_page.php');
            }

            if ($url != '') {
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
    if ($user) {
        if ($addon->getConfig('backend_aktiv') == '1') {
            $session = rex::getUser()->isAdmin();
            $redirect = '';
            if ($session == false) {
                $redirect = "aktiv";
            }
            if ($session == true || rex::getImpersonator()) {
                $redirect = "inaktiv";
            }
            if ($redirect == 'aktiv') {
                $url = $this->getConfig('redirect_backend');
                $mpage = new rex_fragment();
                $mpage = $mpage->parse('maintenance_page_be.php');

                if ($url != '') {
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
    if ($addon->getConfig('backend_aktiv') == '1') {
        rex_extension::register('OUTPUT_FILTER', function (rex_extension_point $magic) {
            $header = '<i class="maintenance rex-icon fa-exclamation-triangle">';
            $replace = '<i title="Mode: Lock Backend" class="rex-icon fa-exclamation-triangle aktivieren_backend">';
            $magic->setSubject(str_replace($header, $replace, $magic->getSubject()));
        });
    }
    if ($addon->getConfig('frontend_aktiv') == 'Aktivieren') {
        rex_extension::register('OUTPUT_FILTER', function (rex_extension_point $ep) {
            $suchmuster = '<i class="maintenance rex-icon fa-exclamation-triangle">';
            $ersetzen = '<i title="Mode: Lock Frontend" class="rex-icon fa-exclamation-triangle aktivieren_frontend">';
            $ep->setSubject(str_replace($suchmuster, $ersetzen, $ep->getSubject()));
        });
    }
    rex_view::addJsFile($this->getAssetsUrl('dist/bootstrap-tokenfield.js'));
    rex_view::addJsFile($this->getAssetsUrl('dist/init_bootstrap-tokenfield.js'));
    rex_view::addCssFile($this->getAssetsUrl('dist/css/bootstrap-tokenfield.css'));
    rex_view::addCssFile($this->getAssetsUrl('css/maintenance.css'));
}
