<?php

namespace FriendsOfREDAXO\Maintenance;

use FriendsOfREDAXO\TwoFactorAuth\one_time_password;
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
use Throwable;

use function count;
use function in_array;

use const FILTER_VALIDATE_IP;
use const FILTER_VALIDATE_URL;

/**
 * Class Maintenance.
 * @package FriendsOfREDAXO\Maintenance
 */
class Maintenance
{
    private static ?rex_addon $addon = null;

    /**
     * Gets the addon instance.
     */
    private static function getAddOn(): rex_addon
    {
        if (null === self::$addon) {
            self::$addon = rex_addon::get('maintenance');
        }
        return self::$addon;
    }

    /**
     * Checks if a URL is valid.
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
     * Checks if an IP address is valid.
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
     * Checks if the current IP is allowed.
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
     * Checks if the current host is allowed.
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
     * Checks if the current YRewrite domain is allowed.
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
     * Checks if the current YRewrite domain is in maintenance mode.
     * @api
     */
    public static function isDomainInMaintenance(): bool
    {
        // Check if all domains are locked globally
        $allDomainsLocked = (bool) self::getConfig('all_domains_locked', false);
        if ($allDomainsLocked) {
            return true;
        }

        // Check individual domain status
        if (!rex_addon::get('yrewrite')->isAvailable()) {
            return false;
        }

        if ($ydomain = rex_yrewrite::getDomainByArticleId(rex_article::getCurrentId(), rex_clang::getCurrentId())) {
            $domainName = $ydomain->getName();
            $domainStatus = (array) self::getConfig('domain_status', []);

            // Check if this specific domain is in maintenance mode
            if (isset($domainStatus[$domainName]) && $domainStatus[$domainName]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the maintenance secret is valid.
     * @api
     */
    public static function isSecretAllowed(): bool
    {
        $config_secret = (string) self::getConfig('maintenance_secret', '');

        // Check if already logged in with the correct secret
        if ('' !== $config_secret && rex_session('maintenance_secret', 'string', '') === $config_secret) {
            return true;
        }

        $maintenance_secret = rex_request('maintenance_secret', 'string', '');
        $authentification_mode = (string) self::getConfig('authentification_mode', '');

        // Authentifizierung prüfen - für URL-Parameter und auch bei leerem Modus
        $authentification_mode = (string) self::getConfig('authentification_mode', '');
        if (('' === $authentification_mode || 'URL' === $authentification_mode || 'password' === $authentification_mode)
            && '' !== $config_secret
            && $maintenance_secret === $config_secret) {
            rex_set_session('maintenance_secret', $maintenance_secret);
            return true;
        }

        rex_unset_session('maintenance_secret');
        return false;
    }

    /**
     * Checks if the current user is allowed.
     * @api
     */
    public static function isUserAllowed(): bool
    {
        rex_backend_login::createUser();
        $user = rex::getUser();

        // Admins always have access, regardless of settings
        if ($user instanceof rex_user && $user->isAdmin()) {
            return true;
        }

        // Check if the REDAXO user should be blocked
        $block_frontend_rex_user = (bool) self::getConfig('block_frontend_rex_user', false);

        // If the user is logged in and should not be blocked, check 2FA requirements
        if ($user instanceof rex_user && !$block_frontend_rex_user) {
            // Check if 2factor_auth addon is active and user has 2FA enabled
            if (self::is2FARequiredAndNotCompleted()) {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Checks if 2FA is required and not completed.
     */
    private static function is2FARequiredAndNotCompleted(): bool
    {
        // Check if 2factor_auth addon is available and active
        // Check if 2factor_auth addon exists and is available
        if (!rex_addon::exists('2factor_auth') || !rex_addon::get('2factor_auth')->isAvailable()) {
            return false;
        }

        // Check if the user has 2FA enabled and if OTP verification is required
        try {
            $otp = one_time_password::getInstance();

            // If 2FA is enabled for the user but not verified, deny access
            if ($otp->isEnabled() && !$otp->isVerified()) {
                return true;
            }
        } catch (Throwable $e) {
            // If there's any error with 2FA checking, allow access (fail-safe)
            return false;
        }

        return false;
    }

    /**
     * Checks frontend access and shows maintenance page if necessary.
     */
    public static function checkFrontend(): void
    {
        rex_login::startSession();

        // Check if the current domain is in maintenance mode (new domain-based logic)
        $domainInMaintenance = self::isDomainInMaintenance();

        // If domain is NOT in maintenance, allow access
        if (!$domainInMaintenance) {
            return;
        }

        // If the IP address is allowed, do not block the request
        if (self::isIpAllowed()) {
            return;
        }

        // If YRewrite is installed and the domain is allowed, do not block the request
        if (rex_addon::get('yrewrite')->isAvailable() && self::isYrewriteDomainAllowed()) {
            return;
        }

        // If the host is allowed, do not block the request
        if (self::isHostAllowed()) {
            return;
        }

        // If the secret/password is correct, do not block the request
        if (self::isSecretAllowed()) {
            return;
        }

        // If the user is allowed (admin or non-blocked editor), do not block the request
        if (self::isUserAllowed()) {
            return;
        }

        // If the sitemap is requested, do not block the request
        $REQUEST_URI = rex_server('REQUEST_URI', 'string', '');

        $allowedUris = [
            '/_clear_cache/_clear_cache.php',
            '/sitemap.xml',
        ];

        // Exclude maintenance mode only for exact paths:
        if (in_array($REQUEST_URI, $allowedUris, true)) {
            // Maintenance mode NOT active – allow request
            return;
        }

        // EP to allow media files
        $media = rex_get('rex_media_file', 'string', '');
        $media_unblock = [];
        $media_unblocklist = rex_extension::registerPoint(new rex_extension_point('MAINTENANCE_MEDIA_UNBLOCK_LIST', $media_unblock));
        if (in_array($media, $media_unblocklist, true)) {
            return;
        }

        // Block everything that has not been allowed so far as chosen in the settings
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
     * Checks backend access and shows maintenance page if necessary.
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
     * Sets maintenance mode indicators in backend.
     */
    public static function setIndicators(): void
    {
        $page = self::getAddOn()->getProperty('page');

        if (self::getBoolConfig('block_backend', false)) {
            $page['title'] .= ' <span class="label label-info pull-right">B</span>';
            $page['icon'] .= ' fa-toggle-on block_backend';
            self::getAddOn()->setProperty('page', $page);
        }

        if (self::getBoolConfig('block_frontend', false)) {
            $page['title'] .= ' <span class="label label-danger pull-right">F</span>';
            $page['icon'] .= ' fa-toggle-on block_frontend';
        }

        // Check for domain-based maintenance
        $domainStatus = (array) self::getConfig('domain_status', []);
        $allDomainsLocked = (bool) self::getConfig('all_domains_locked', false);
        $activeDomains = array_filter($domainStatus);

        if ($allDomainsLocked || !empty($activeDomains)) {
            $count = $allDomainsLocked ? 'All' : count($activeDomains);
            $page['title'] .= ' <span class="label label-warning pull-right" title="Domain-Wartung aktiv">D:' . $count . '</span>';
            $page['icon'] .= ' fa-sitemap';
        }

        self::getAddOn()->setProperty('page', $page);
    }

    /**
     * Shows maintenance announcement.
     * @api
     */
    public static function showAnnouncement(): void
    {
        echo self::getAnnouncement();
    }

    /**
     * Gets maintenance announcement if within announcement period.
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
     * Gets config value with type casting.
     */
    private static function getConfig(string $key, mixed $default = null): mixed
    {
        return self::getAddOn()->getConfig($key, $default);
    }

    /**
     * Gets boolean config value.
     */
    private static function getBoolConfig(string $key, bool $default = false): bool
    {
        return (bool) self::getConfig($key, $default);
    }
}
