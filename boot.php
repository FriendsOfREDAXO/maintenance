<?php
$addon = rex_addon::get('maintenance');
if (!rex::isBackend()) {
	$ips = "";
	$ips = explode (", ", $this->getConfig('ip'));
	if ($addon->getConfig('frontend_aktiv') == 'Aktivieren') {
		$session = rex_backend_login::hasSession();
		$redirect ='';
		if ($this->getConfig('ip')!='' && in_array($_SERVER['REMOTE_ADDR'],$ips)) {
			$ipcheck="failed";
			$redirect = "aktiv"; 
  		}
		if (!$session or $ipcheck =='failed') {
			$redirect = "aktiv";
  		}
  		if (in_array($_SERVER['REMOTE_ADDR'],$ips)) {
			$redirect = "inaktiv"; 
  		}
  		if ($session) {
			$redirect = "inaktiv";
  		}
  		if ($redirect=='aktiv') {
			$url = $this->getConfig('redirect_frontend');
			rex_response::sendRedirect($url);
  		}
  	} 
  	if ($addon->getConfig('frontend_aktiv') == 'Selfmade') {
  		$session = rex_backend_login::hasSession();
		$selfmade ='';
  		if ($this->getConfig('ip')!='' && in_array($_SERVER['REMOTE_ADDR'],$ips)) {
			$ipcheck="failed";
			$selfmade = "aktiv"; 
  		}
  		if (!$session or $ipcheck =='failed') {
			$selfmade = "aktiv";
  		}
  		if (in_array($_SERVER['REMOTE_ADDR'],$ips)) {
			$selfmade = "inaktiv"; 
  		}
  		if ($session) {
			$selfmade = "inaktiv";
  		}
  		if ($selfmade=='aktiv') {
			$check = $this->getConfig('frontend_aktiv');
    		$this->setConfig('frontend_aktiv', $check);
  		}
  	}

}

if(rex::isBackend()) {
	$user = rex::getUser();
	$checkIP = new maintenance_functions();
	if($user) {
    	if($addon->getConfig('backend_aktiv') == '1') {
			$session = rex::getUser()->isAdmin();
			$redirect ='';
			if ($session == false) {
				$redirect = "aktiv"; 
	  		}
			if ($session == true) {
				$redirect = "inaktiv";
	  		}
	  		if ($redirect=='aktiv') {
				$url = $this->getConfig('redirect_backend');
				rex_response::sendRedirect($url);
	  		}
		}
	}

rex_view::addJsFile($this->getAssetsUrl('dist/bootstrap-tokenfield.js'));
rex_view::addJsFile($this->getAssetsUrl('dist/init_bootstrap-tokenfield.js'));
rex_view::addCssFile($this->getAssetsUrl('dist/css/bootstrap-tokenfield.css'));
rex_view::addCssFile($this->getAssetsUrl('dist/css/tokenfield-typeahead.css'));

}


