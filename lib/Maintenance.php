<?php

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

/**
 * Class Maintenance
 * @package FriendsOfREDAXO\Maintenance
 */
class Maintenance
{
    /** @var rex_addon */
    private static rex_addon $addon;

    static {
        self::$addon = rex_addon::get('maintenance');
    }

    /**
     * Checks if a URL is valid
     * @param string $url
     * @return bool|null
     * @api
     */
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

    /**
     * Checks if an IP address is valid
     * @param string $ip
     * @return bool|null
     * @api
     */
    public function checkIp(string $ip): ?bool
    {
        if ('' !== $ip && false === filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }
        return true;
    }

    /**
     * Checks if the current IP is allowed
     * @return bool
     * @api
     */
    public static function isIpAllowed(): bool
    {
        $ip = rex_server('REMOTE_ADDR', 'string', '');
        $allowedIps = (string) self::getConfig('allowed_ips', '');

        if ('' !== $allowedIps) {
            $allowedIpsArray = explode(',', $allowedIps);
            return in_array($ip, $allowedIpsArray, true);
        }

        return false;
    }

    /**
     * Checks if the current host is allowed
     * @return bool
     * @api
     */
    public static function isHostAllowed(): bool
    {
        $host = rex_server('HTTP_HOST', 'string', '');
        $allowedHosts = (string) self::getConfig('allowed_domains', '');

        if ('' !== $allowedHosts) {
            $allowedHostsArray = explode(',', $allowedHosts);
            return in_array($host, $allowedHostsArray, true);
        }

        return false;
    }

    /**
     * Checks if the current YRewrite domain is allowed
     * @return bool
     * @api
     */
    public static function isYrewriteDomainAllowed(): bool
    {
        if ($ydomain = rex_yrewrite::getDomainByArticleId(rex_article::getCurrentId(), rex_clang::getCurrentId())) {
            $yrewrite_domain = $ydomain->getHost();
            $allowedDomains = (string) self::getConfig('allowed_yrewrite_domains', '');

            if ('' !== $allowedDomains) {
                $allowedDomainsArray = explode('|', $allowedDomains);
                return in_array($yrewrite_domain, $allowedDomainsArray, true);
            }
        }

        return false;
    }

    /**
     * Checks if the maintenance secret is valid
     * @return bool
     * @api
     */
    public static function isSecretAllowed(): bool
    {
        $config_secret = (string) self::getConfig('maintenance_secret', '');

        // Prüfen ob bereits mit richtigem Secret eingeloggt
        if ('' !== $config_secret && rex_session('maintenance_secret', 'string', '') === $config_secret) {
            return true;
        }

        $maintenance_secret = rex_request('maintenance_secret', 'string', '');
        $authentification_mode = (string) self::getConfig('authentification_mode', '');

        // Prüfen ob korrektes Secret per URL oder Passwort übergeben wurde
        if (('URL' === $authentification_mode || 'password' === $authentification_mode) && '' !== $config_secret && $maintenance_secret === $config_secret) {
            rex_set_session('maintenance_secret', $maintenance_secret);
            return true;
        }

        rex_unset_session('maintenance_secret');
        return false;
    }

    /**
     * Checks if the current user is allowed
     * @return bool
     * @api
     */
    public static function isUserAllowed(): bool
    {
        rex_backend_login::createUser();
        $user = rex::getUser();

        // Admins haben immer Zugriff, unabhängig von Einstellungen
        if ($user instanceof rex_user && $user->isAdmin()) {
            return true;
        }

        // Prüfen ob der REDAXO-Benutzer gesperrt werden soll
        $block_frontend_rex_user = (bool) self::getConfig('block_frontend_rex_user', false);
        
        // Wenn Benutzer eingeloggt ist und nicht gesperrt werden soll, dann Zugriff erlauben
        if ($user instanceof rex_user && !$block_frontend_rex_user) {
            return true;
        }

        return false;
    }

    /**
     * Checks frontend access and shows maintenance page if necessary
     * @return void
     */
    public static function checkFrontend(): void
    {
        rex_login::startSession();

        // Wenn die IP-Adresse erlaubt ist, Anfrage nicht sperren
        if (self::isIpAllowed()) {
            return;
        }

        // Wenn YRewrite installiert und Domain erlaubt ist, Anfrage nicht sperren
        if (rex_addon::get('yrewrite')->isAvailable() && self::isYrewriteDomainAllowed()) {
            return;
        }

        // Wenn der Host erlaubt ist, Anfrage nicht sperren
        if (self::isHostAllowed()) {
            return;
        }

        // Wenn das Secret / Passwort stimmt, Anfrage nicht sperren
        if (self::isSecretAllowed()) {
            return;
        }

        // Wenn der Benutzer zugelassen ist (Admin oder nicht-gesperrter Redakteur), Anfrage nicht sperren
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
        if (in_array($media, $media_unblocklist, true)) {
            return;
        }

        // Alles, was bis hier hin nicht erlaubt wurde, blockieren wie in den Einstellungen gewählt
        $redirect_url = (string) self::getConfig('redirect_frontend_to_url', '');
        $responsecode = (int) self::getConfig('http_response_code', 503);

        $mpage = new rex_fragment();
        if ('' !== $redirect_url) {
            rex_response::setStatus(rex_response::HTTP_MOVED_TEMPORARILY);
            rex_response::sendRedirect($redirect_url);
        }
        
        header('HTTP/1.1 ' . $responsecode);
        exit($mpage->parse('maintenance/frontend.php'));
    }

    /**
     * Checks backend access and shows maintenance page if necessary
     * @return void
     */
    public static function checkBackend(): void
    {
        if (rex::getUser() instanceof rex_user && !rex::getUser()->isAdmin() && !rex::getImpersonator()) {
            $redirect_url = (string) self::getConfig('redirect_backend_to_url', '');
            if ('' !== $redirect_url) {
                rex_response::sendRedirect($redirect_url);
            }
            $mpage = new rex_fragment();
            $responsecode = (int) self::getConfig('http_response_code', 503);
            header('HTTP/1.1 ' . $responsecode);
            exit($mpage->parse('maintenance/backend.php'));
        }
    }

    /**
     * Sets maintenance mode indicators in backend
     * @return void
     */
    public static function setIndicators(): void
    {
        $page = self::$addon->getProperty('page');

        if (self::getBoolConfig('block_backend', false)) {
            $page['title'] .= ' <span class="label label-info pull-right">B</span>';
            $page['icon'] .= ' fa-toggle-on block_backend';
            self::$addon->setProperty('page', $page);
        }

        if (self::getBoolConfig('block_frontend', false)) {
            $page['title'] .= ' <span class="label label-danger pull-right">F</span>';
            $page['icon'] .= ' fa-toggle-on block_frontend';
        }

        self::$addon->setProperty('page', $page);
    }

    /**
     * Shows maintenance announcement
     * @return void
     * @api
     */
    public static function showAnnouncement(): void
    {
        echo self::getAnnouncement();
    }

    /**
     * Gets maintenance announcement if within announcement period
     * @return string
     * @api
     */
    public static function getAnnouncement(): string
    {
        $start_date = (string) self::getConfig('announcement_start_date', '');
        if ('' !== $start_date) {
            $start = strtotime($start_date);
            $end = strtotime((string) self::getConfig('announcement_end_date', ''));
            $now = time();
            if ($start && $end && $now >= $start && $now <= $end) {
                return (string) self::getConfig('announcement', '');
            }
        }

        return '';
    }

    /**
     * Gets config value with type casting
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private static function getConfig(string $key, mixed $default = null): mixed 
    {
        return self::$addon->getConfig($key, $default);
    }

    /**
     * Gets boolean config value
     * @param string $key
     * @param bool $default
     * @return bool
     */
    private static function getBoolConfig(string $key, bool $default = false): bool
    {
        return (bool) self::getConfig($key, $default);
    }
}
