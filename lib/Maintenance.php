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
use rex_response;

class Maintenance
{
    /** @api */
    public function checkUrl(string $url): ?bool
    {
        if ($url !== '') {
            if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                return false;
            }
            return true;
        }
        return null;
    }
    /** @api */
    public function checkIp(string $ip): ?bool
    {
        if($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        } else {
            return true;
        }
        
    }

    /** @api */
    public static function isIpAllowed() : bool
    {
        $addon = \rex_addon::get('maintenance');
        $ip = rex_server('REMOTE_ADDR', 'string', '');
        $allowedIps = strval($addon->getConfig('allowed_ips')); // @phpstan-ignore-line

        if ($allowedIps !== '') {
            $allowedIpsArray = explode(',', $allowedIps);
            return in_array($ip, $allowedIpsArray, true);
        }

        return false;
    }

    /** @api */
    public static function isHostAllowed() : bool
    {
        $addon = \rex_addon::get('maintenance');
        $host = rex_server('HTTP_HOST', 'string', '');
        $allowedHosts = strval($addon->getConfig('allowed_hosts', false)); // @phpstan-ignore-line

        if ($allowedHosts !== '') {
            $allowedHostsArray = explode(',', $allowedHosts);
            return in_array($host, $allowedHostsArray, true);
        }

        return false;
    }

    /** @api */
    public static function isYrewriteDomainAllowed() :bool
    {
        $addon = \rex_addon::get('maintenance');

        if(\rex_yrewrite::getCurrentDomain()) {
            $yrewrite_domain = \rex_yrewrite::getCurrentDomain()->getHost();
            $allowedDomains = strval($addon->getConfig('allowed_domains')); // @phpstan-ignore-line

            if ($allowedDomains !== '') {
                $allowedDomainsArray = explode(',', $allowedDomains);
                return in_array($yrewrite_domain, $allowedDomainsArray, true);
            }
        }


        return false;
    }

    /** @api */
    public static function isSecretAllowed() :bool
    {
        $addon = \rex_addon::get('maintenance');

        // Bereits mit richtigem Secret eingeloggt
        if(\rex_session('secret', 'string', false) === strval($addon->getConfig('secret'))) { // @phpstan-ignore-line 
            return true;
        }
        $secret = rex_request('secret', 'string', false);

        if ($addon->getConfig('type') === 'secret' && $secret === strval($addon->getConfig('secret'))) { // @phpstan-ignore-line
            rex_set_session('secret', $secret);
            return true;
        }

        rex_set_session('secret', false);
        return false;

    }

    /** @api */
    public static function isUserAllowed() :bool
    {
        $addon = \rex_addon::get('maintenance');
        \rex_backend_login::createUser();
        $user = rex::getUser();

        // Admins dürfen sich immer einloggen
        if ($user instanceof \rex_user && $user->isAdmin()) {
            return true;
        }

        // Eingeloggte REDAXO-Benutzer dürfen sich einloggen, wenn es in den Einstellungen erlaubt ist
        if($user instanceof \rex_user && boolval($addon->getConfig('allow_logged_in_users'))) {
            return true;
        }

        return false;
    }

    public static function checkFrontend() : void
    {
        $addon = \rex_addon::get('maintenance');
        // Wenn Maintenance-Modus aktiviert ist und das Frontend blockiert werden soll
        if (boolval($addon->getConfig('block_frontend'))) {

            \rex_login::startSession();

            // Wenn die IP-Adresse erlaubt ist, Anfrage nicht sperren
            if (self::isIpAllowed()) {
                return;
            }

            // Wenn die YRewrite-Domain erlaubt ist, Anfrage nicht sperren
            if (self::isYrewriteDomainAllowed()) {
                return;
            }

            // Wenn die Host erlaubt ist, Anfrage nicht sperren
            if (self::isHostAllowed()) {
                return;
            }

            // Wenn das Secret stimmt, Anfrage nicht sperren
            if(self::isSecretAllowed()) {
                return;
            }

            // Wenn eingeloggte REDAXO-Benutzer erlaubt sind, oder der Benutzer Admin ist, Anfrage nicht sperren
            if (self::isUserAllowed()) {
                return;
            }

            // Wenn die Sitemap angefordert wird, Anfrage nicht sperren
            $REQUEST_URI = rex_server('REQUEST_URI', 'string', '');
            if (str_contains($REQUEST_URI, 'sitemap.xml') === true) {
                return;
            }

            // EP zum Erlauben von Medien-Dateien
            $media = rex_get('rex_media_file', 'string', '');
            $media_unblock = [];
            $media_unblocklist = \rex_extension::registerPoint(new \rex_extension_point('MAINTENANCE_MEDIA_UNBLOCK_LIST', $media_unblock));
            // @phpstan-ignore-next-line
            if (in_array($media, $media_unblocklist, true)) {
                return;
            }

            // Alles, was bis hier hin nicht erlaubt wurde, blockieren wie in den Einstellungen gewählt
            $redirect_url = strval($addon->getConfig('redirect_frontend_to_url')); // @phpstan-ignore-line
            $responsecode = strval($addon->getConfig('http_response_code')); // @phpstan-ignore-line

            $mpage = new \rex_fragment();
            if(strval($addon->getConfig('authentification_mode')) === 'login') { // @phpstan-ignore-line
                echo $mpage->parse('maintenance_page_pw_form.php');
            } elseif ($redirect_url !== '') {
                rex_response::setStatus(rex_response::HTTP_MOVED_TEMPORARILY);
                rex_response::sendRedirect($redirect_url);
            }
            header('HTTP/1.1 ' . $responsecode);
            echo $mpage->parse('maintenance_page.php');
            die();

        }

    }

    public static function checkBackend() :void
    {
        $addon = \rex_addon::get('maintenance');
        if ($addon->getConfig('block_backend') === true && rex::getUser() instanceof \rex_user && !rex::getUser()->isAdmin()) {
            if (boolval($addon->getConfig('redirect_backend_to_url'))) {
                rex_response::sendRedirect(strval($addon->getConfig('redirect_backend_to_url')));  // @phpstan-ignore-line
            }
        }
    }

    public static function setIndicators() : void
    {
        $addon = \rex_addon::get('maintenance');

        if (boolval($addon->getConfig('block_backend'))) {
            \rex_extension::register('OUTPUT_FILTER', function (\rex_extension_point $ep) {
                $header = '<i class="maintenance rex-icon fa-toggle-off">';
                $replace = '<i class="maintenance rex-icon fa-toggle-on" data-maintenance="backend">';
                $subject = $ep->getSubject();
                if (is_string($subject)) {
                    $out = str_replace($header, $replace, $subject);
                    $ep->setSubject($out);
                }
            });
        }
        if (boolval($addon->getConfig('block_frontend'))) {
            \rex_extension::register('OUTPUT_FILTER', function (\rex_extension_point $ep) {
                $suchmuster = '<i class="maintenance rex-icon fa-toggle-off">';
                $ersetzen = '<i class="maintenance rex-icon fa-toggle-on" data-maintenance="frontend">';
                $subject = $ep->getSubject();
                if (is_string($subject)) {
                    $out = str_replace($suchmuster, $ersetzen, $subject);
                    $ep->setSubject($out);
                }
            });
        }
    }

    /** @api */
    public static function showAnnouncement() :void
    {
        echo self::getAnnouncement();
    }
    
    /** @api */
    public static function getAnnouncement() :string
    {
        $addon = \rex_addon::get('maintenance');

        if(strval($addon->getConfig('announcement_start_date')) !== '') {  // @phpstan-ignore-line
            $start = strtotime(strval($addon->getConfig('announcement_start_date'))); // @phpstan-ignore-line
            $end = strtotime(strval($addon->getConfig('announcement_end_date'))); // @phpstan-ignore-line
            $now = time();
            if($now >= $start && $now <= $end) {
                return strval($addon->getConfig('announcement')); // @phpstan-ignore-line
            }
        }

        return '';
    }
}
