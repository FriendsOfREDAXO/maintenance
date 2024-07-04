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
// stop if Setup is active
if (rex::isSetup()) {
    return;
}

$addon = rex_addon::get('maintenance');


if(rex::isFrontend()) {
    \FriendsOfREDAXO\Maintenance\Maintenance::checkFrontend();
}

if(rex::isBackend()) {
    \FriendsOfREDAXO\Maintenance\Maintenance::checkBackend();
    \FriendsOfREDAXO\Maintenance\Maintenance::setIndicators();

//    rex_view::addJsFile($addon->getAssetsUrl('dist/bootstrap-tokenfield.js'));
//    rex_view::addJsFile($addon->getAssetsUrl('dist/init_bootstrap-tokenfield.js'));
//    rex_view::addCssFile($addon->getAssetsUrl('dist/css/bootstrap-tokenfield.css'));
    rex_view::addCssFile($addon->getAssetsUrl('css/maintenance.css'));

}
