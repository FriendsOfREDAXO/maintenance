<?php
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class rex_maintenance_command_on extends rex_console_command
{
    protected function configure()
    {
        $this->setAliases(['frontend:on'])
            ->setDescription('Sets frontend maintenance mode on');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);
        $io->title('Maintenance AddOn');
        $addon = rex_addon::get('maintenance');
        $addon->setConfig('frontend_aktiv', 'Aktivieren');
        $io->success('maintenance mode activated');
        return 0;
    }
}

