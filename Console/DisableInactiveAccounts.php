<?php

declare(strict_types=1);
namespace Aligent\Pci4Compatibility\Console;

use Aligent\Pci4Compatibility\Model\DisableInactiveAdminAccounts;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisableInactiveAccounts extends Command
{

    /**
     * @param DisableInactiveAdminAccounts $disableInactiveAdminAccounts
     * @param string|null $name
     */
    public function __construct(
        private readonly DisableInactiveAdminAccounts $disableInactiveAdminAccounts,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('aligent:pci4:disable-inactive-accounts');
        $this->setDescription('Disable admin accounts with 90 days of inactivity');
        parent::configure();
    }

    /**
     * Disable admin accounts with 90 days of inactivity
     *
     * Used to be able to satisfy PCI DSS 4.0.1 requirement 8.2.6
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $output->writeln(__("Disabling any admin accounts with 90 days of inactivity"));
            $this->disableInactiveAdminAccounts->execute();
            $output->writeln(__("Disabling of accounts complete"));
            return Cli::RETURN_SUCCESS;
        } catch (\Throwable $t) {
            $output->writeln(__("<error>Error: {$t->getMessage()}</error>"));
            return Cli::RETURN_FAILURE;
        }
    }
}
