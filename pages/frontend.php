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
$maintenance_functions = new maintenance_functions();
$content = '';

if (rex_post('config-submit', 'boolean')) {
    $addon->setConfig(rex_post('config', [
        ['url', 'string'],
    ]));
    $addon->setConfig(rex_post('config', [
        ['secret', 'string'],
    ]));
    $addon->setConfig(rex_post('config', [
    	['blockSession', 'string'],
    ]));
	$addon->setConfig(rex_post('config', [
        ['ip', 'string'],
    ]));
    $addon->setConfig(rex_post('config', [
        ['frontend_aktiv', 'string'],
    ]));
    $addon->setConfig(rex_post('config', [
        ['redirect_frontend', 'string'],
    ]));
	$ips = explode (", ", $this->getConfig('ip'));
      
    $content .= rex_view::info('Änderung gespeichert');

}	


$ips = explode (", ", $this->getConfig('ip'));
	foreach($ips as $ip) {
		if($maintenance_functions->CheckIp($ip) === false) {
			echo rex_view::warning('Falsche IP: '. $ip);
		}
	}

  if($maintenance_functions->checkUrl($this->getConfig('redirect_frontend')) === true) {
		$content .= rex_view::info('Änderung gespeichert');
	}
	if($maintenance_functions->checkUrl($this->getConfig('redirect_frontend')) === false) {
		$content .=	rex_view::warning('Falscher Link');
		$addon->setConfig('redirect_frontend', '');
	}




$content .=  '
<div class="rex-form">
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        <fieldset>';

$formElements = [];
$n2 = [];
$n2['label'] = '<label for="rex-maintenance-secret-secret-we-got-a-secret">'.$this->i18n('secret').'</label>';
$n2['field'] = $this->i18n("secret-secret").'</br></br><input class="form-control" type="text" id="rex-maintenance-secret-secret-we-got-a-secret" name="config[secret]" value="' . $addon->getConfig('secret') . '"/><i>'.$this->i18n("secret-example").' meine-website.de/?secret=EingetragenesWort</i>';

$formElements[] = $n2;

$n1 = [];
$n1['label'] = '<label for="rex-maintenance-ip">'.$this->i18n('IP').'</label>';
$n1['field'] = $this->i18n("ipErk").'</br></br><input class="form-control test" type="text" id="rex-maintenance-ip" name="config[ip]" value="' . $addon->getConfig('ip') . '"/><i>'.$this->i18n("ipAkt").$_SERVER['REMOTE_ADDR'].'</i>';

$formElements[] = $n1;
$n = [];
$n['label'] = '<label for="rex-maintenance-redirectUrl">'.$this->i18n('redirectUrl').'</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-maintenance-redirectUrl" name="config[redirect_frontend]" placeholder="https://example.com" value="' . $addon->getConfig('redirect_frontend') . '"/>';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="blocken">' . $this->i18n('blockSession') . '</label>';
$select = new rex_select();
$select->setId('blockSession');
$select->setAttribute('class', 'form-control selectpicker');
$select->setName('config[blockSession]');
$select->addOption($this->i18n('session_Inaktiv'), 'Inaktiv');
$select->addOption($this->i18n('session_Redakteure'), 'Redakteure');

$select->setSelected($this->getConfig('blockSession'));
$n['field'] = $select->get() .'</br><i>Sollen Redakteure trotz Backend-Session aus dem Frontend ausgesperrt werden?</i>';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="frontend">' . $this->i18n('deakt-front') . '</label>';
$select = new rex_select();
$select->setId('deakt-front');
$select->setAttribute('class', 'form-control selectpicker');
$select->setName('config[frontend_aktiv]');
$select->addOption($this->i18n('Frontend_entsperren'), 'Deaktivieren');
$select->addOption($this->i18n('Frontend_Sperren'), 'Aktivieren');
$select->addOption($this->i18n('Eigene_Loesung'), 'Selfmade');

$select->setSelected($this->getConfig('frontend_aktiv'));
$n['field'] = $select->get();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '
        </fieldset>

        <fieldset class="rex-form-action">
        ';

$formElements = [];

$n = [];
$n['field'] = '<div class="btn-toolbar"><button id="rex-maintenance-save" type="submit" name="config-submit" class="btn btn-save rex-form-aligned" value="1">Einstellungen speichern</button></div>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/submit.php');

$content .= '
        </fieldset>

    </form>
</div>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit');
$fragment->setVar('title', 'Maintenance-Settings');
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

?>
