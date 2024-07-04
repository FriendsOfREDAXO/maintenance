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

class Maintenance extends \rex_addon
{
    
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
    public function checkIp(string $ip): ?bool
    {
        if($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        } else {
            return true;
        }
        
    }

    public function isIpAllowed() : bool{
        $ip = rex_server('REMOTE_ADDR', 'string', '');
        $allowedIps = $this->getConfig('allowed_ips');

        if ($allowedIps !== '') {
            $allowedIpsArray = explode(',', $allowedIps);
            return in_array($ip, $allowedIpsArray);
        }

        return false;
    }

    public function isHostAllowed() : bool{
        $host = rex_server('HTTP_HOST', 'string', '');
        $allowedHosts = $this->getConfig('allowed_hosts');

        if ($allowedHosts !== '') {
            $allowedHostsArray = explode(',', $allowedHosts);
            return in_array($host, $allowedHostsArray);
        }

        return false;
    }

    public function isYrewriteDomainAllowed() :bool {
        $yrewrite_domain = \rex_yrewrite::getCurrentDomain()->getHost();
        $allowedDomains = $this->getConfig('allowed_domains');

        if ($allowedDomains !== '') {
            $allowedDomainsArray = explode(',', $allowedDomains);
            return in_array($yrewrite_domain, $allowedDomainsArray);
        }

        return false;
    }

    public function isSecretAllowed() :bool {

        // Bereits mit richtigem Secret eingeloggt
        if(\rex_session('secret', 'string', false) === $this->getConfig('secret')) {
            return true;
        }
        $secret = rex_request('secret', 'string', false);

        if ($this->getConfig('type') === 'secret' && $secret === $this->getConfig('secret')) {
            rex_set_session('secret', $secret);
            return true;
        }

        rex_set_session('secret', false);
        return false;

    }

    public function isUserAllowed() {
        \rex_backend_login::createUser();
        $user = rex::getUser();

        // Admins dürfen sich immer einloggen
        if ($user && $user->isAdmin()) {
            return true;
        }

        // Eingeloggte REDAXO-Benutzer dürfen sich einloggen, wenn es in den Einstellungen erlaubt ist
        if($user && $this->getConfig('allow_logged_in_users')) {
            return true;
        }

        return false;
    }

    public static function checkFrontend() : void
    {
        /* @var $addon FriendsOfREDAXO\Maintenance\Maintenance */
        /* @var FriendsOfREDAXO\Maintenance\Maintenance $addon */
        $addon = self::get('maintenance');
        // Wenn Maintenance-Modus aktiviert ist und das Frontend blockiert werden soll
        if ($addon->getConfig('block_frontend')) {

            \rex_login::startSession();

            // Wenn die IP-Adresse erlaubt ist, Anfrage nicht sperren
            if ($addon->isIpAllowed()) {
                return;
            }

            // Wenn die YRewrite-Domain erlaubt ist, Anfrage nicht sperren
            if ($addon->isYrewriteDomainAllowed()) {
                return;
            }

            // Wenn die Host erlaubt ist, Anfrage nicht sperren
            if ($addon->isHostAllowed()) {
                return;
            }

            // Wenn das Secret stimmt, Anfrage nicht sperren
            if($addon->isSecretAllowed()) {
                return;
            }

            // Wenn eingeloggte REDAXO-Benutzer erlaubt sind, oder der Benutzer Admin ist, Anfrage nicht sperren
            if ($addon->isUserAllowed()) {
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
            if ($media !== '' && count($media_unblocklist) > 0) {
                if (in_array($media, $media_unblocklist)) {
                    return;
                }
            }

            // Alles, was bis hier hin nicht erlaubt wurde, blockieren wie in den Einstellungen gewählt
            $redirect_url = $addon->getConfig('redirect_frontend_to_url');
            $responsecode = $addon->getConfig('http_response_code');

            $mpage = new \rex_fragment();
            if($addon->getConfig('authentification_mode') === 'login') {
                echo $mpage->parse('maintenance_page_pw_form.php');
            } else if ($redirect_url) {
                rex_response::setStatus(rex_response::HTTP_MOVED_TEMPORARILY);
                rex_response::sendRedirect($redirect_url);
            }
            header('HTTP/1.1 ' . $responsecode);
            echo $mpage->parse('maintenance_page.php');
            die();

        }

    }

    public static function checkBackend()
    {
        $addon = self::get('maintenance');
        if ($addon->getConfig('block_backend') === true && rex::getUser() && !rex::getUser()->isAdmin()) {
            if ($addon->getConfig('redirect_backend_to_url') !== '') {
                rex_response::sendRedirect($addon->getConfig('redirect_backend_to_url'));
                exit;
            }
        }
    }

    public static function setIndicators()
    {
        $addon = self::get('maintenance');

        if ($addon->getConfig('block_backend')) {
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
        if ($addon->getConfig('block_frontend')) {
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

    public static function showAnnouncement() :void
    {
        echo self::getAnnouncement();
    }
    
    public static function getAnnouncement() :string
    {
        $addon = self::get('maintenance');

        if($addon->getConfig('announcement_start_date') !== '') {
            $start = strtotime($addon->getConfig('announcement_start_date'));
            $end = strtotime($addon->getConfig('announcement_end_date'));
            $now = time();
            if($now >= $start && $now <= $end) {
                return $addon->getConfig('announcement');
            }
        }
    }
}
