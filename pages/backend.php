
	
<?php

$addon = rex_addon::get('maintenance');
$checkLink = new maintenance_functions();
$content = '';


if (rex_post('config-submit', 'boolean')) {
	$addon->setConfig(rex_post('config', [
        ['redirect_backend', 'string'],
    ]));

    $addon->setConfig(rex_post('config', [
        ['backend_aktiv', 'string'],
    ]));
    if($checkLink->checkUrl($addon->getConfig('redirect_backend')) === true) {
		$content .= rex_view::info('Änderung gespeichert');
	}
	if($checkLink->checkUrl($addon->getConfig('redirect_backend')) === false) {
		$content .=	rex_view::warning('Falscher Link');
		$addon->setConfig('redirect_backend', '');
	}
$newURL = rex_url::currentBackendPage();
// Umleitung auf die aktuelle Seite auslösen
rex_response::sendRedirect($newURL);
}			
 
$content .=  '
<div class="rex-form">
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        <fieldset>';

$formElements = [];

$n = [];
$n['label'] = '<label for="redirectUrl">'.$this->i18n('redirectUrl').'</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-maintenance-redirectUrl" name="config[redirect_backend]" placeholder="https://example.com" value="' . $addon->getConfig('redirect_backend') . '"/>';
$formElements[] = $n;

$n2 = [];
$n2['label'] = '<label for="redakteure_ausschließen">' . $this->i18n('deakt-reda') . '</label>';
$n2['field'] = '<input type="checkbox" id="rex-maintenance-aktiv" name="config[backend_aktiv]"' . (!empty($this->getConfig('backend_aktiv')) && $this->getConfig('backend_aktiv') == '1' ? ' checked="checked"' : '') . ' value="1" />';
$formElements[] = $n2;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '
        </fieldset>

        <fieldset class="rex-form-action">';

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
