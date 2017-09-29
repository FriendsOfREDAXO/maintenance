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
$addon = rex_addon::get('maintenance');
if (!rex::isBackend()) {
	$ips = "";
	$ips = explode (", ", $this->getConfig('ip'));
	if ($addon->getConfig('frontend_aktiv') == 'Aktivieren') {
		$session = rex_backend_login::hasSession();
		$redirect ='inaktiv';
		if (rex_backend_login::createUser()) {
		    $admin = rex::getUser()->isAdmin();
		}
		if($addon->getConfig('blockSession') == 'Inaktiv') {
			$redirect = 'inaktiv';
		}
		if($addon->getConfig('blockSession') == 'Inaktiv' && in_array($_SERVER['REMOTE_ADDR'],$ips)) {
			$redirect = 'inaktiv';
		}	
		if ($addon->getConfig('blockSession') == "Redakteure" && $admin == false && !in_array($_SERVER['REMOTE_ADDR'],$ips)) {
			$redirect = 'aktiv';
		}
		if ($addon->getConfig('blockSession') == "Redakteure" && $admin == true) {
			$redirect = 'inaktiv';
		}
  		if (!$session) {
  			$redirect = "aktiv";
  		}
  		if (in_array($_SERVER['REMOTE_ADDR'],$ips)) {
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
			$selfmade = "aktiv"; 
  		}
  		if (!$session) {
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
	if($addon->getConfig('backend_aktiv') == '1') {
		rex_extension::register('OUTPUT_FILTER',function(rex_extension_point $magic){
			$header = '<i class="rex-icon fa-exclamation-triangle">';
			$replace = '<i title="Mode: Lock Backend" class="rex-icon fa-exclamation-triangle aktivieren_backend">';
			$magic->setSubject(str_replace($header, $replace, $magic->getSubject()));
		});
	}
	if($addon->getConfig('frontend_aktiv') == 'Aktivieren') {
		rex_extension::register('OUTPUT_FILTER',function(rex_extension_point $ep){
			$suchmuster = '<i class="rex-icon fa-exclamation-triangle">';
			$ersetzen = '<i title="Mode: Lock Frontend" class="rex-icon fa-exclamation-triangle aktivieren_frontend">';
			$ep->setSubject(str_replace($suchmuster, $ersetzen, $ep->getSubject()));
		});
	}
	if($addon->getConfig('frontend_aktiv') == 'Selfmade') {
	    rex_extension::register('OUTPUT_FILTER',function(rex_extension_point $ep){
			$suchmuster = '<i class="rex-icon fa-exclamation-triangle">';
			$ersetzen = '<i title="Mode: Own Solution" class="rex-icon fa-exclamation-triangle selfmade_frontend">';
			$ep->setSubject(str_replace($suchmuster, $ersetzen, $ep->getSubject()));
	    });
	}
rex_view::addJsFile($this->getAssetsUrl('dist/bootstrap-tokenfield.js'));
rex_view::addJsFile($this->getAssetsUrl('dist/init_bootstrap-tokenfield.js'));
rex_view::addCssFile($this->getAssetsUrl('dist/css/bootstrap-tokenfield.css'));
rex_view::addCssFile($this->getAssetsUrl('css/maintenance.css'));
}


