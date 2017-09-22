<?php
if (!$this->hasConfig()) {
    $this->setConfig('ip', '');
    $this->setConfig('frontend_aktiv', 'Deaktivieren');
    $this->setConfig('redirect_frontend', '');
    $this->setConfig('redirect_backend', '');
    $this->setConfig('backend_aktiv', '0');
    $this->setConfig('blockSession', 'Inaktiv');
}
