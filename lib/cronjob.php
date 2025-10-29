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

use FriendsOfREDAXO\Maintenance\Maintenance;

/**
 * Cronjob to check and apply scheduled maintenance mode.
 * This is optional - the scheduled maintenance also works via request-based checking.
 * Use this cronjob for more precise timing (e.g., run every minute).
 */
class rex_cronjob_scheduled_maintenance extends rex_cronjob
{
    public function execute()
    {
        // Call the scheduled maintenance checker
        Maintenance::checkScheduledMaintenance();

        $addon = rex_addon::get('maintenance');
        $scheduledStart = (string) $addon->getConfig('scheduled_start', '');
        $scheduledEnd = (string) $addon->getConfig('scheduled_end', '');
        $blockFrontend = (bool) $addon->getConfig('block_frontend', false);

        // Log what happened
        if ('' !== $scheduledStart || '' !== $scheduledEnd) {
            if ($blockFrontend) {
                $this->setMessage('Wartungsmodus ist aktiv (geplant bis: ' . $scheduledEnd . ')');
            } else {
                $this->setMessage('Wartungsmodus ist inaktiv (geplanter Start: ' . $scheduledStart . ')');
            }
        } else {
            $this->setMessage('Keine geplante Wartung konfiguriert');
        }

        return true;
    }

    public function getTypeName()
    {
        return rex_i18n::msg('maintenance_cronjob_scheduled_name');
    }

    public function getParamFields()
    {
        return [];
    }
}
