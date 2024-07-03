<?php
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class rex_maintenance_command_off extends rex_console_command
{
    protected function configure(): void
    {
        $this->setAliases(['frontend:on'])
            ->setDescription(rex_i18n::msg('maintenance_command_on_description'));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);
        $io->title(rex_i18n::msg('maintenance_title'));
        $addon = rex_addon::get('maintenance');
        $addon->setConfig('block_frontend', false);
        $io->success(rex_i18n::msg('maintenance_mode_deactivated'));
        return 0;
    }
}
