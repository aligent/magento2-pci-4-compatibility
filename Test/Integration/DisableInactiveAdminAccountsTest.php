<?php

declare(strict_types=1);
namespace Aligent\Pci4Compatibility\Test\Integration;

use Aligent\Pci4Compatibility\Model\DisableInactiveAdminAccounts;
use Magento\TestFramework\ObjectManager;
use Magento\User\Model\ResourceModel\User as UserResource;
use PHPUnit\Framework\TestCase;

class DisableInactiveAdminAccountsTest extends TestCase
{

    /**
     * @var DisableInactiveAdminAccounts|null
     */
    private ?DisableInactiveAdminAccounts $disableInactiveAdminAccounts = null;
    /**
     * @var UserResource|null
     */
    private ?UserResource $userResource = null;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = ObjectManager::getInstance();
        $this->disableInactiveAdminAccounts = $objectManager->create(DisableInactiveAdminAccounts::class);
        $this->userResource = $objectManager->create(UserResource::class);
    }

    /**
     * Ensure that accounts not accessed in 90 days are disabled
     *
     * @magentoDataFixture Aligent_Pci4Compatibility::Test/_files/inactive_admin_account.php
     *
     * @return void
     * @throws \Exception
     */
    public function testInactiveAccountIsDisabled(): void
    {
        // Trigger the DisableInactiveAdminAccounts functionality
        $this->disableInactiveAdminAccounts->execute();

        $userData = $this->userResource->loadByUsername('inactiveAdmin1');
        $status = (int)($userData['is_active'] ?? 1);
        $this->assertEquals(0, $status, 'Inactive admin account inactiveAdmin1 was not disabled.');
    }

    /**
     * Ensure that accounts not used in 90 days since creation are disabled
     *
     * @magentoDataFixture Aligent_Pci4Compatibility::Test/_files/unused_inactive_admin_account.php
     *
     * @return void
     * @throws \Exception
     */
    public function testUnusedInactiveAccountIsDisabled(): void
    {
        // Trigger the DisableInactiveAdminAccounts functionality
        $this->disableInactiveAdminAccounts->execute();

        $userData = $this->userResource->loadByUsername('inactiveAdmin2');
        $status = (int)($userData['is_active'] ?? 1);
        $this->assertEquals(0, $status, 'Inactive admin account inactiveAdmin2 was not disabled.');
    }

    /**
     * Ensure that accounts accessed in 90 days are not disabled
     *
     * @magentoDataFixture Aligent_Pci4Compatibility::Test/_files/active_admin_account.php
     *
     * @return void
     * @throws \Exception
     */
    public function testActiveAccountIsNotDisabled(): void
    {
        // Trigger the DisableInactiveAdminAccounts functionality
        $this->disableInactiveAdminAccounts->execute();

        $userData = $this->userResource->loadByUsername('activeAdmin1');
        $status = (int)($userData['is_active'] ?? 0);
        $this->assertEquals(1, $status, 'Active admin account activeAdmin1 was disabled.');
    }

    /**
     * Ensure that accounts created less than 90 days ago are not disabled
     *
     * @magentoDataFixture Aligent_Pci4Compatibility::Test/_files/unused_active_admin_account.php
     *
     * @return void
     * @throws \Exception
     */
    public function testUnusedActiveAccountIsNotDisabled(): void
    {
        // Trigger the DisableInactiveAdminAccounts functionality
        $this->disableInactiveAdminAccounts->execute();

        $userData = $this->userResource->loadByUsername('activeAdmin2');
        $status = (int)($userData['is_active'] ?? 0);
        $this->assertEquals(1, $status, 'Active admin account activeAdmin2 was disabled.');
    }
}
