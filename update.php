<?php

$addon = rex_addon::get('maintenance');

// Upgrade from 2.x to 3.x
if (rex_version::compare($addon->getVersion(), '3.0.0-dev', '<')) {
    if ($addon->hasConfig('responsecode')) {
        $addon->setConfig('http_response_code', $addon->getConfig('responsecode'));
    }

    if ($addon->hasConfig('ip')) {
        $addon->setConfig('allowed_ips', $addon->getConfig('ip'));
    }

    if ($addon->hasConfig('frontend_aktiv')) {
        $addon->setConfig('block_frontend', 'Deaktivieren' === $addon->getConfig('frontend_aktiv') ? 0 : 1);
    }

    if ($addon->hasConfig('redirect_frontend')) {
        $addon->setConfig('redirect_frontend_to_url', $addon->getConfig('redirect_frontend'));
    }

    if ($addon->hasConfig('redirect_backend')) {
        $addon->setConfig('redirect_backend_to_url', $addon->getConfig('redirect_backend'));
    }

    if ($addon->hasConfig('backend_aktiv')) {
        $addon->setConfig('block_backend', '1' === $addon->getConfig('backend_aktiv') ? 1 : 0);
    }

    if ($addon->hasConfig('blockSession')) {
        $addon->setConfig('block_frontend_rex_user', 'Inaktiv' === $addon->getConfig('blockSession') ? 0 : 1);
    }

    if ($addon->hasConfig('type')) {
        $addon->setConfig('authentication_mode', 'Password' === $addon->getConfig('type') ? 'password' : 'URL');
    }

    if ($addon->hasConfig('secret')) {
        $addon->setConfig('maintenance_secret', $addon->getConfig('maintenance_secret'));
    }

    $addon->removeConfig('responsecode');
    $addon->removeConfig('ip');
    $addon->removeConfig('frontend_aktiv');
    $addon->removeConfig('redirect_frontend');
    $addon->removeConfig('redirect_backend');
    $addon->removeConfig('backend_aktiv');
    $addon->removeConfig('blockSession');
    $addon->removeConfig('type');
    $addon->removeConfig('secret');
}

// Leerer String ('') und 'URL' werden beide als gültige URL-Authentifizierung betrachtet
$authentication_mode = $addon->getConfig('authentication_mode', '');
if (!in_array($authentication_mode, ['URL', 'password'], true)) {
    // Wenn kein gültiger Modus gesetzt ist, standardmäßig auf URL setzen
    $addon->setConfig('authentication_mode', 'URL');
}

// Migration von 'authentification_mode' zu 'authentication_mode' (Rechtschreibkorrektur)
if ($addon->hasConfig('authentification_mode') && !$addon->hasConfig('authentication_mode')) {
    $addon->setConfig('authentication_mode', $addon->getConfig('authentification_mode'));
    $addon->removeConfig('authentification_mode');
}

// Überprüfen, ob ein maintenance_secret existiert
if (!$addon->hasConfig('maintenance_secret') || '' === $addon->getConfig('maintenance_secret')) {
    // Falls kein Secret vorhanden, ein neues generieren
    $addon->setConfig('maintenance_secret', bin2hex(random_bytes(16)));
}

// Migration der alten allowed_yrewrite_domains zu neuem domain_status System
if ($addon->hasConfig('allowed_yrewrite_domains') && !$addon->hasConfig('domain_status')) {
    $oldAllowedDomains = (string) $addon->getConfig('allowed_yrewrite_domains', '');

    if ('' !== $oldAllowedDomains && rex_addon::exists('yrewrite') && rex_addon::get('yrewrite')->isAvailable()) {
        // Die alten allowed_yrewrite_domains waren eine Whitelist (erlaubte Domains)
        // Im neuen System bedeutet: Domains die NICHT in der Whitelist sind, sollten gesperrt sein
        $allowedDomainsArray = explode('|', $oldAllowedDomains);
        $allowedDomainsArray = array_filter(array_map('trim', $allowedDomainsArray));

        $domainStatus = [];
        foreach (rex_yrewrite::getDomains() as $domain) {
            $domainName = $domain->getName();
            if ('default' !== $domainName) {
                // Domain ist gesperrt, wenn sie NICHT in der Whitelist war
                $domainStatus[$domainName] = !in_array($domain->getHost(), $allowedDomainsArray, true);
            }
        }

        if (!empty($domainStatus)) {
            $addon->setConfig('domain_status', $domainStatus);
        }
    }

    // Alte Konfiguration kann entfernt werden (bleibt aber zur Kompatibilität)
    // $addon->removeConfig('allowed_yrewrite_domains');
}
