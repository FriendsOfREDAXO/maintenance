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

// Register cronjob type only if cronjob addon is available
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

// Impersonate-Warnung über OUTPUT_FILTER: Bootstrap 3 Modal (einmal pro Session)
if (rex::isBackend()) {
    rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) {
        $addon = rex_addon::get('maintenance');

        // Nur wenn Backend-Wartungsmodus aktiv ist
        if (!(bool) $addon->getConfig('block_backend', false)) {
            return $ep->getSubject();
        }

        // Prüfen ob wir im Impersonate-Modus sind
        $impersonator = rex::getImpersonator();
        if (!$impersonator instanceof rex_user) {
            return $ep->getSubject();
        }

        // Warnung nur anzeigen wenn der aktuelle Benutzer kein Admin ist
        $currentUser = rex::getUser();
        if ($currentUser instanceof rex_user && $currentUser->isAdmin()) {
            return $ep->getSubject();
        }

        // Anzeige vorbereiten (sichere Übergabe der Texte an JS mittels rex_escape)
        $title = $addon->i18n('maintenance_impersonate_warning_title');
        $userName = $currentUser instanceof rex_user ? ($currentUser->getName() ?: $currentUser->getLogin()) : 'Unknown User';
        $message = $addon->i18n('maintenance_impersonate_warning_message', $userName);

        $content = $ep->getSubject();

        // Bootstrap 3 Modal für elegante Anzeige der Warnung
        // Anzeige erfolgt nur einmal pro Browser-Session (sessionStorage).
        $modalId = 'maintenance-impersonate-modal-' . uniqid();
        
        $modalHtml = '
        <!-- Maintenance Impersonate Warning Modal -->
        <div class="modal fade" id="' . $modalId . '" tabindex="-1" role="dialog" aria-labelledby="' . $modalId . '-label">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="background-color: #f0ad4e; color: white;">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="' . $modalId . '-label">
                            <i class="rex-icon rex-icon-warning"></i> ' . rex_escape($title) . '
                        </h4>
                    </div>
                    <div class="modal-body">
                        <p>' . rex_escape($message) . '</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">' . $addon->i18n('maintenance_impersonate_ok') . '</button>
                    </div>
                </div>
            </div>
        </div>';
        
        $warningScript = '
        <script type="text/javascript" nonce="' . rex_response::getNonce() . '">
        (function(){
            try {
                if (!sessionStorage.getItem("maintenance_impersonate_warning_shown")) {
                    jQuery(function($){
                        // Modal nach DOM-Laden anzeigen
                        $("#' . $modalId . '").modal({
                            backdrop: "static",
                            keyboard: false
                        });
                        
                        // Session-Flag setzen wenn Modal geschlossen wird
                        $("#' . $modalId . '").on("hidden.bs.modal", function() {
                            sessionStorage.setItem("maintenance_impersonate_warning_shown", "1");
                        });
                    });
                }
            } catch(e) {
                // Fallback: nichts tun
            }
        })();
        </script>';
        
        // Modal HTML und Script vor dem schließenden body-Tag einfügen
        if (strpos($content, '</body>') !== false) {
            $content = str_replace('</body>', $modalHtml . $warningScript . '</body>', $content);
        }
        
        return $content;
    }, rex_extension::LATE);
}
