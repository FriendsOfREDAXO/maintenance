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
class maintenance_functions { 
	
	public function CheckUrl(string $url): ?bool {
		if ($url !== '') {
			if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) { 
				return false;	
			}
			return true;
		}
		return null;
	}
	public function CheckIp(string $ip): ?bool {
		if($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP) === FALSE){
		return false; 	
		}
		else {
			return true; 
		}
		
	}
}
