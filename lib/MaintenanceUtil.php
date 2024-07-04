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

class Maintenance
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
}
