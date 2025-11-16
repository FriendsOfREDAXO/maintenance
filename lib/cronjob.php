<?php

/**
 * @package redaxo\maintenance
 */

// Nur laden wenn cronjob Addon existiert und verfügbar ist
if (!rex_addon::exists('cronjob') || !rex_addon::get('cronjob')->isAvailable()) {
    return;
}

class rex_cronjob_scheduled_maintenance extends rex_cronjob
{
    public function execute()
    {
        $addon = rex_addon::get('maintenance');
        $scheduledStart = $addon->getConfig('scheduled_start', '');
        $scheduledEnd = $addon->getConfig('scheduled_end', '');

        if ('' === $scheduledStart && '' === $scheduledEnd) {
            $this->setMessage('Keine geplante Wartung konfiguriert');
            return true;
        }

        $now = time();
        $start = $scheduledStart ? strtotime($scheduledStart) : null;
        $end = $scheduledEnd ? strtotime($scheduledEnd) : null;
        $blockFrontend = (bool) $addon->getConfig('block_frontend', false);

        // Check if we should activate maintenance
        if ($start && $end && $now >= $start && $now < $end && !$blockFrontend) {
            $addon->setConfig('block_frontend', true);
            $this->setMessage('Wartungsmodus aktiviert (bis: ' . $scheduledEnd . ')');
            return true;
        }

        // Check if we should deactivate maintenance
        if ($end && $now >= $end && $blockFrontend) {
            $addon->setConfig('block_frontend', false);
            $addon->setConfig('scheduled_start', '');
            $addon->setConfig('scheduled_end', '');
            $this->setMessage('Wartungsmodus deaktiviert (geplante Wartung beendet)');
            return true;
        }

        // No change needed
        if ($blockFrontend) {
            $this->setMessage('Wartungsmodus läuft (bis: ' . $scheduledEnd . ')');
        } else {
            $this->setMessage('Wartungsmodus inaktiv (geplanter Start: ' . $scheduledStart . ')');
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
