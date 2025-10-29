<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class rex_maintenance_mode_command extends rex_console_command
{
    #[Override]
    protected function configure(): void
    {
        $this
            ->setName('maintenance:mode')
            ->setDescription(rex_i18n::msg('maintenance_mode_command_description'))
            ->addArgument(
                'action',
                InputArgument::REQUIRED,
                'Action: status, frontend, backend, domain, all',
            )
            ->addArgument(
                'state',
                InputArgument::OPTIONAL,
                'State: on/off (for frontend, backend, all) or domain name (for domain)',
            )
            ->addOption(
                'lock',
                'l',
                InputOption::VALUE_NONE,
                'Lock domain (use with domain action)',
            )
            ->addOption(
                'unlock',
                'u',
                InputOption::VALUE_NONE,
                'Unlock domain (use with domain action)',
            );
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);
        $io->title(rex_i18n::msg('maintenance_title'));

        $action = $input->getArgument('action');
        $state = $input->getArgument('state');
        $addon = rex_addon::get('maintenance');

        switch ($action) {
            case 'status':
                return $this->showStatus($io, $addon);

            case 'frontend':
                return $this->toggleFrontend($io, $addon, $state);

            case 'backend':
                return $this->toggleBackend($io, $addon, $state);

            case 'domain':
                return $this->handleDomain($io, $addon, $state, $input->getOption('lock'), $input->getOption('unlock'));

            case 'all':
                return $this->toggleAll($io, $addon, $state);

                // Legacy support: "on" and "off" as direct arguments
            case 'on':
            case 'off':
                return $this->toggleFrontend($io, $addon, $action);

            default:
                $io->error('Invalid action. Use: status, frontend, backend, domain, all');
                return Command::INVALID;
        }
    }

    private function showStatus(OutputInterface $io, rex_addon $addon): int
    {
        $blockFrontend = $addon->getConfig('block_frontend', 0);
        $blockBackend = $addon->getConfig('block_backend', 0);
        $allDomainsLocked = $addon->getConfig('all_domains_locked', false);
        $domainStatus = $addon->getConfig('domain_status', []);

        $io->section('Current Status');

        $io->text([
            'Frontend: ' . ($blockFrontend ? '<fg=red>LOCKED</>' : '<fg=green>Open</>'),
            'Backend: ' . ($blockBackend ? '<fg=red>LOCKED</>' : '<fg=green>Open</>'),
            'All Domains: ' . ($allDomainsLocked ? '<fg=red>LOCKED</>' : '<fg=green>Open</>'),
        ]);

        if (!empty($domainStatus) && rex_addon::exists('yrewrite') && rex_addon::get('yrewrite')->isAvailable()) {
            $io->section('Domain Status');
            $lockedDomains = [];
            foreach ($domainStatus as $domain => $locked) {
                if ($locked) {
                    $lockedDomains[] = $domain;
                }
            }
            if (!empty($lockedDomains)) {
                $io->listing($lockedDomains);
            } else {
                $io->text('No domains locked');
            }
        }

        return Command::SUCCESS;
    }

    private function toggleFrontend(OutputInterface $io, rex_addon $addon, ?string $state): int
    {
        if (!$state || !in_array($state, ['on', 'off'])) {
            $io->error('Invalid state. Use: on or off');
            return Command::INVALID;
        }

        $currentState = $addon->getConfig('block_frontend', 0);
        $newState = ('on' === $state) ? 1 : 0;

        if ($currentState === $newState) {
            $io->info('Frontend maintenance is already ' . $state);
            return Command::FAILURE;
        }

        $addon->setConfig('block_frontend', $newState);
        $io->success('Frontend maintenance ' . ($newState ? 'activated' : 'deactivated'));
        return Command::SUCCESS;
    }

    private function toggleBackend(OutputInterface $io, rex_addon $addon, ?string $state): int
    {
        if (!$state || !in_array($state, ['on', 'off'])) {
            $io->error('Invalid state. Use: on or off');
            return Command::INVALID;
        }

        $currentState = $addon->getConfig('block_backend', 0);
        $newState = ('on' === $state) ? 1 : 0;

        if ($currentState === $newState) {
            $io->info('Backend maintenance is already ' . $state);
            return Command::FAILURE;
        }

        $addon->setConfig('block_backend', $newState);
        $io->success('Backend maintenance ' . ($newState ? 'activated' : 'deactivated'));
        return Command::SUCCESS;
    }

    private function handleDomain(OutputInterface $io, rex_addon $addon, ?string $domain, bool $lock, bool $unlock): int
    {
        if (!rex_addon::exists('yrewrite') || !rex_addon::get('yrewrite')->isAvailable()) {
            $io->error('YRewrite addon is not installed or not available');
            return Command::FAILURE;
        }

        if (!$domain) {
            $io->error('Domain name required');
            return Command::INVALID;
        }

        if ($lock === $unlock) {
            $io->error('Use either --lock or --unlock option');
            return Command::INVALID;
        }

        $domains = rex_yrewrite::getDomains();
        if (!isset($domains[$domain])) {
            $io->error('Domain "' . $domain . '" not found');
            return Command::FAILURE;
        }

        $domainStatus = $addon->getConfig('domain_status', []);
        $domainStatus[$domain] = $lock ? 1 : 0;
        $addon->setConfig('domain_status', $domainStatus);

        $io->success('Domain "' . $domain . '" ' . ($lock ? 'locked' : 'unlocked'));
        return Command::SUCCESS;
    }

    private function toggleAll(OutputInterface $io, rex_addon $addon, ?string $state): int
    {
        if (!$state || !in_array($state, ['on', 'off'])) {
            $io->error('Invalid state. Use: on or off');
            return Command::INVALID;
        }

        $newState = ('on' === $state) ? 1 : 0;

        $addon->setConfig('block_frontend', $newState);
        $addon->setConfig('block_backend', $newState);
        $addon->setConfig('all_domains_locked', (bool) $newState);

        $io->success('All maintenance modes ' . ($newState ? 'activated' : 'deactivated'));
        return Command::SUCCESS;
    }
}
