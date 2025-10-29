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

// Register cronjob type
if (rex_addon::get('cronjob')->isAvailable()) {
    rex_cronjob_manager::registerType(rex_cronjob_scheduled_maintenance::class);
}
rex_extension::register('PACKAGES_INCLUDED', static function () {
    $addon = rex_addon::get('maintenance');

    if (rex::isFrontend() && ((bool) $addon->getConfig('block_frontend') || Maintenance::isDomainInMaintenance())) {
        Maintenance::checkFrontend();
    }
    if (rex::isBackend() && (bool) $addon->getConfig('block_backend')) {
        Maintenance::checkBackend();
    }

    if (rex::isBackend()) {
        Maintenance::setIndicators();

        rex_view::addCssFile($addon->getAssetsUrl('css/maintenance.css'));
        rex_view::addCssFile($addon->getAssetsUrl('css/maintenance-icons.css'));

        if ('maintenance/frontend' === rex_be_controller::getCurrentPage()
            || 'maintenance/frontend/index' === rex_be_controller::getCurrentPage()
            || 'maintenance/frontend/scheduled' === rex_be_controller::getCurrentPage()) {
            rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) {
                $suchmuster = 'class="###maintenance-settings-editor###"';
                $ersetzen = (string) rex_config::get('maintenance', 'editor'); // @phpstan-ignore-line
                $ep->setSubject(str_replace($suchmuster, $ersetzen, $ep->getSubject())); // @phpstan-ignore-line
            });
        }
    }
});
