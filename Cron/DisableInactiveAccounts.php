<?php

declare(strict_types=1);
namespace Aligent\Pci4Compatibility\Cron;

use Aligent\Pci4Compatibility\Model\DisableInactiveAdminAccounts;

class DisableInactiveAccounts
{

    /**
     * @param DisableInactiveAdminAccounts $inactiveUsers
     */
    public function __construct(
        private readonly DisableInactiveAdminAccounts $inactiveUsers,
    ) {
    }

    /**
     * Cron job that simply runs service to disable inactive admin accounts.
     *
     * This is to satisfy PCI DSS 4.0.1 requirement 8.2.6
     *
     * @return void
     */
    public function execute(): void
    {
        $this->inactiveUsers->execute();
    }
}
