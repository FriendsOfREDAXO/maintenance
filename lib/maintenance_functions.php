<?php

class maintenance_functions { 
	
	public function checkUrl($url) {
		if ($url) {
			if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) { 
				return false;	
			}
			return true;
		}
	}
	public function checkIp($ip) {
		if($ip){
			if (filter_var($ip, FILTER_VALIDATE_IP)) {
	    		return true;
			}
			return false;
		}
	}
}
?>