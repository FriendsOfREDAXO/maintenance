<?php
$addon = rex_addon::get('maintenance');
if (!rex::isBackend()) {
	if ($addon->getConfig('aktiv') == '1') {
		$session = rex_backend_login::hasSession();
		
		$redirect ='';
		
		if ($this->getConfig('ip')!='' && $this->getConfig('ip')!=$_SERVER['REMOTE_ADDR']) {
			$ipcheck="failed";
			$redirect = "aktiv"; 
  		}
  		
		if (!$session or $ipcheck =='failed') {
			$redirect = "aktiv";
  		}

  		if ($this->getConfig('ip')==$_SERVER['REMOTE_ADDR']) {
			$redirect = "inaktiv"; 
  		}
  		
  		if ($session) {
			$redirect = "inaktiv";
  		}
  		
  		if ($redirect=='aktiv') {
			header('Location: ' . $this->getConfig('url'));
			exit;
  		}
  		
  		
  	}
}

