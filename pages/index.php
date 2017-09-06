<?php
$addon = rex_addon::get('maintenance');

$content = '';
echo rex_view::title($this->i18n('title'));

if (rex_post('config-submit', 'boolean')) {
    $addon->setConfig(rex_post('config', [
        ['url', 'string'],
    ]));
	$addon->setConfig(rex_post('config', [
        ['ip', 'string'],
    ]));
    
    $addon->setConfig(rex_post('config', [
        ['aktiv', 'string'],
    ]));

    $content .= rex_view::info('Änderung gespeichert');
}

$content .=  '
<div class="rex-form">
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        <fieldset>';

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-maintenance-url">URL</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-maintenance-url" name="config[url]" value="' . $addon->getConfig('url') . '"/>';
$formElements[] = $n;

$n1['label'] = '<label for="rex-maintenance-url">IP</label>';
$n1['field'] = '<input class="form-control" type="text" id="rex-maintenance-ip" name="config[ip]" value="' . $addon->getConfig('ip') . '"/><i>IP:'.$_SERVER['REMOTE_ADDR'].'</i>';
$formElements[] = $n1;

$n2 = [];
$n2['label'] = '<label for="demo_addon-config-checkbox">Aktivieren</label>';
$n2['field'] = '<input type="checkbox" id="rex-maintenance-aktiv" name="config[aktiv]"' . (!empty($this->getConfig('aktiv')) && $this->getConfig('aktiv') == '1' ? ' checked="checked"' : '') . ' value="1" />';
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

