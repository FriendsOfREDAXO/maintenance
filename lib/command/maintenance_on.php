<?php
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class rex_maintenance_command_on extends rex_console_command
{
    protected function configure(): void
    {
        $this->setAliases(['frontend:off'])
            ->setDescription(rex_i18n::msg('maintenance_command_off_description'));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);
        $io->title('Maintenance AddOn');
        $addon = rex_addon::get('maintenance');
        $addon->setConfig('block_frontend', true);
        $io->success(rex_i18n::msg('maintenance_mode_activated'));
        return 0;
    }
}
