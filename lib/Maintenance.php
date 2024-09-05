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

namespace FriendsOfREDAXO\Maintenance;

use rex;
use rex_addon;
use rex_article;
use rex_backend_login;
use rex_clang;
use rex_extension;
use rex_extension_point;
use rex_fragment;
use rex_login;
use rex_response;
use rex_user;
use rex_yrewrite;

use function in_array;

use const FILTER_VALIDATE_IP;
use const FILTER_VALIDATE_URL;

class Maintenance
{
    /** @api */
    public function checkUrl(string $url): ?bool
    {
        if ('' !== $url) {
            if (false === filter_var($url, FILTER_VALIDATE_URL)) {
                return false;
            }
            return true;
        }
        return null;
    }

    /** @api */
    public function checkIp(string $ip): ?bool
    {
        if ('' !== $ip && false === filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }
        return true;
    }

    /** @api */
    public static function isIpAllowed(): bool
    {
        $addon = rex_addon::get('maintenance');
        $ip = rex_server('REMOTE_ADDR', 'string', '');
        $allowedIps = (string) $addon->getConfig('allowed_ips'); // @phpstan-ignore-line

        if ('' !== $allowedIps) {
            $allowedIpsArray = explode(',', $allowedIps);
            return in_array($ip, $allowedIpsArray, true);
        }

        return false;
    }

    /** @api */
    public static function isHostAllowed(): bool
    {
        $addon = rex_addon::get('maintenance');
        $host = rex_server('HTTP_HOST', 'string', '');
        $allowedHosts = (string) $addon->getConfig('allowed_yrewrite_domains', false); // @phpstan-ignore-line

        if ('' !== $allowedHosts) {
            $allowedHostsArray = explode(',', $allowedHosts);
            return in_array($host, $allowedHostsArray, true);
        }

        return false;
    }

    /** @api */
    public static function isYrewriteDomainAllowed(): bool
    {
        $addon = rex_addon::get('maintenance');
        if ($ydomain = rex_yrewrite::getDomainByArticleId(rex_article::getCurrentId(), rex_clang::getCurrentId())) {
            $yrewrite_domain = $ydomain->getHost();
            $allowedDomains = (string) $addon->getConfig('allowed_yrewrite_domains'); // @phpstan-ignore-line

            if ('' !== $allowedDomains) {
                $allowedDomainsArray = explode('|', $allowedDomains);
                return in_array($yrewrite_domain, $allowedDomainsArray, true);
            }
        }

        return false;
    }

    /** @api */
    public static function isSecretAllowed(): bool
    {
        $addon = rex_addon::get('maintenance');
        $config_secret = (string) $addon->getConfig('maintenance_secret');

        // Bereits mit richtigem Secret eingeloggt
        if ('' != $config_secret && rex_session('maintenance_secret', 'string', '') === $config_secret) { // @phpstan-ignore-line
            return true;
        }

        $maintenance_secret = rex_request('maintenance_secret', 'string', '');
        $authentification_mode = $addon->getConfig('authentification_mode');

        if (('URL' === $authentification_mode || 'password' === $authentification_mode) && '' != $config_secret && $maintenance_secret === $config_secret) {
            rex_set_session('maintenance_secret', $maintenance_secret);
            return true;
        }

        rex_unset_session('maintenance_secret');
        return false;
    }

    /** @api */
    public static function isUserAllowed(): bool
    {
        $addon = rex_addon::get('maintenance');
        rex_backend_login::createUser();
        $user = rex::getUser();

        // Admins dürfen sich immer einloggen
        if ($user instanceof rex_user && $user->isAdmin()) {
            return true;
        }

        // Eingeloggte REDAXO-Benutzer dürfen sich einloggen, wenn es in den Einstellungen erlaubt ist
        if ($user instanceof rex_user && (bool) $addon->getConfig('allow_logged_in_users')) {
            return true;
        }

        return false;
    }

    public static function checkFrontend(): void
    {
        $addon = rex_addon::get('maintenance');

        rex_login::startSession();

        // Wenn die IP-Adresse erlaubt ist, Anfrage nicht sperren
        if (self::isIpAllowed()) {
            return;
        }

        // Wenn die YRewrite installiert und Domain erlaubt ist, Anfrage nicht sperren
        if (rex_addon::get('yrewrite')->isAvailable()) {
            if (self::isYrewriteDomainAllowed()) {
                return;
            }
        }

        // Wenn die Host erlaubt ist, Anfrage nicht sperren
        if (self::isHostAllowed()) {
            return;
        }

        // Wenn das Secret / Passwort stimmt, Anfrage nicht sperren
        if (self::isSecretAllowed()) {
            return;
        }

        // Wenn eingeloggte REDAXO-Benutzer erlaubt sind, oder der Benutzer Admin ist, Anfrage nicht sperren
        if (self::isUserAllowed()) {
            return;
        }

        // Wenn die Sitemap angefordert wird, Anfrage nicht sperren
        $REQUEST_URI = rex_server('REQUEST_URI', 'string', '');
        if (true === str_contains($REQUEST_URI, 'sitemap.xml')) {
            return;
        }

        // EP zum Erlauben von Medien-Dateien
        $media = rex_get('rex_media_file', 'string', '');
        $media_unblock = [];
        $media_unblocklist = rex_extension::registerPoint(new rex_extension_point('MAINTENANCE_MEDIA_UNBLOCK_LIST', $media_unblock));
        // @phpstan-ignore-next-line
        if (in_array($media, $media_unblocklist, true)) {
            return;
        }

        // Alles, was bis hier hin nicht erlaubt wurde, blockieren wie in den Einstellungen gewählt
        $redirect_url = (string) $addon->getConfig('redirect_frontend_to_url'); /** @phpstan-ignore-line */
        $responsecode = (string) $addon->getConfig('http_response_code'); /** @phpstan-ignore-line */

        $mpage = new rex_fragment();
        if ('' !== $redirect_url) {
            rex_response::setStatus(rex_response::HTTP_MOVED_TEMPORARILY);
            rex_response::sendRedirect($redirect_url);
        }
        header('HTTP/1.1 ' . $responsecode);
        exit($mpage->parse('maintenance/frontend.php'));
    }

    public static function checkBackend(): void
    {
        $addon = rex_addon::get('maintenance');

        if (rex::getUser() instanceof rex_user && !rex::getUser()->isAdmin() && !rex::getImpersonator()) {
            if ((string) $addon->getConfig('redirect_backend_to_url')) { // @phpstan-ignore-line
                rex_response::sendRedirect((string) $addon->getConfig('redirect_backend_to_url'));  // @phpstan-ignore-line
            }
            $mpage = new rex_fragment();
            header('HTTP/1.1 ' . (string) $addon->getConfig('http_response_code')); // @phpstan-ignore-line
            exit($mpage->parse('maintenance/backend.php'));
        }
    }

    public static function setIndicators(): void
    {
        $addon = rex_addon::get('maintenance');
        $page = $addon->getProperty('page');

        if ((bool) $addon->getConfig('block_backend')) {
            $page['title'] .= ' <span class="label label-info pull-right">B</span>';
            $page['icon'] .= ' fa-toggle-on block_backend';
            $addon->setProperty('page', $page);
        }

        if ((bool) $addon->getConfig('block_frontend')) {
            $page['title'] .= ' <span class="label label-danger pull-right">F</span>';
            $page['icon'] .= ' fa-toggle-on block_frontend';
        }

        $addon->setProperty('page', $page);
    }

    /** @api */
    public static function showAnnouncement(): void
    {
        echo self::getAnnouncement();
    }

    /** @api */
    public static function getAnnouncement(): string
    {
        $addon = rex_addon::get('maintenance');

        if ('' !== (string) $addon->getConfig('announcement_start_date')) {  /** @phpstan-ignore-line */
            $start = strtotime((string) $addon->getConfig('announcement_start_date')); /** @phpstan-ignore-line */
            $end = strtotime((string) $addon->getConfig('announcement_end_date')); /** @phpstan-ignore-line */
            $now = time();
            if ($now >= $start && $now <= $end) {
                return (string) $addon->getConfig('announcement'); // @phpstan-ignore-line
            }
        }

        return '';
    }
}
