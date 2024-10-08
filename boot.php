<?php

use FriendsOfREDAXO\Maintenance\Maintenance;

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
rex_extension::register('PACKAGES_INCLUDED', static function () {
    $addon = rex_addon::get('maintenance');

    if (rex::isFrontend() && (bool) $addon->getConfig('block_frontend')) {
        Maintenance::checkFrontend();
    }
    if (rex::isBackend() && (bool) $addon->getConfig('block_backend')) {
        Maintenance::checkBackend();
    }

    if (rex::isBackend()) {
        Maintenance::setIndicators();
        rex_view::addJsFile($addon->getAssetsUrl('dist/bootstrap-tokenfield.js'));
        rex_view::addJsFile($addon->getAssetsUrl('dist/init_bootstrap-tokenfield.js'));
        rex_view::addCssFile($addon->getAssetsUrl('dist/css/bootstrap-tokenfield.css'));

        rex_view::addCssFile($addon->getAssetsUrl('css/maintenance.css'));

        if ('maintenance/frontend' === rex_be_controller::getCurrentPage()) {
            rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) {
                $suchmuster = 'class="###maintenance-settings-editor###"';
                $ersetzen = (string) rex_config::get('maintenance', 'editor'); // @phpstan-ignore-line
                $ep->setSubject(str_replace($suchmuster, $ersetzen, $ep->getSubject())); // @phpstan-ignore-line
            });
        }
    }
});
