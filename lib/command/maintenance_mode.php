<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
                "state",
                InputArgument::OPTIONAL,
                rex_i18n::msg('maintenance_mode_command_state_description'),
            );
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);
        $io->title(rex_i18n::msg('maintenance_title'));

        $state = $input->getArgument('state');

        $addon = rex_addon::get('maintenance');
        $currentBlockFrontendConfiguration = $addon->getConfig();

        if ($currentBlockFrontendConfiguration['block_frontend'] === 1 && $state === "on") {
            $io->info(rex_i18n::msg('maintenance_mode_already_activated'));
            return Command::FAILURE;
        }

        if ($currentBlockFrontendConfiguration['block_frontend'] === 0 && $state === "off") {
            $io->info(rex_i18n::msg('maintenance_mode_already_deactivated'));
            return Command::FAILURE;
        }

        if ($state === "on") {
            $addon->setConfig('block_frontend', 1);
            $io->success(rex_i18n::msg('maintenance_mode_activated'));
            return Command::SUCCESS;
        } elseif ($state === "off") {
            $addon->setConfig('block_frontend', 0);
            $io->success(rex_i18n::msg('maintenance_mode_deactivated'));
            return Command::SUCCESS;
        } else {
            $io->error(rex_i18n::msg('maintenance_mode_invalid'));
            return Command::INVALID;
        }
    }
}
