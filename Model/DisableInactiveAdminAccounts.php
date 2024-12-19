<?php

declare(strict_types=1);
namespace Aligent\Pci4Compatibility\Model;

use DateInterval;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\User\Model\ResourceModel\User as UserResource;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use Magento\User\Model\User;
use Psr\Log\LoggerInterface;

class DisableInactiveAdminAccounts
{
    private const INACTIVE_DAYS = 90;

    /**
     * @param TimezoneInterface $localeDate
     * @param UserCollectionFactory $userCollectionFactory
     * @param UserResource $userResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly TimezoneInterface $localeDate,
        private readonly UserCollectionFactory $userCollectionFactory,
        private readonly UserResource $userResource,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Sets the account of any admin user with no login in the last 90 days to inactive.
     *
     * This is used to be able to satisfy PCI DSS 4.0.1 requirement 8.2.6
     *
     * @return void
     */
    public function execute(): void
    {
        try {
            $currentDate = $this->localeDate->date()->sub(new DateInterval('P' . self::INACTIVE_DAYS . 'D'));
            $utcDateTime = $this->localeDate->convertConfigTimeToUtc($currentDate);
        } catch (\Exception $e) {
            $this->logger->critical(
                __METHOD__ . ': Could not get cutoff date for inactivity: ' . $e->getMessage(),
                ['exception' => $e]
            );
            return;
        }

        // find users that have been inactive for 90 days
        $userCollection = $this->userCollectionFactory->create();
        // don't need to disable accounts that are already disabled
        $userCollection->addFieldToFilter('status', '1');
        $userCollection->addFieldToFilter('logdate', ['lt' => $utcDateTime]);
        $users = $userCollection->getItems();
        foreach ($users as $user) {
            /** @var User $user */
            $this->disableUserAccount($user);
        }

        // find user accounts older than 90 days that have never logged in
        $userCollection = $this->userCollectionFactory->create();
        $userCollection->addFieldToFilter('status', '1');
        $userCollection->addFieldToFilter('logdate', ['null' => true]);
        $userCollection->addFieldToFilter('created', ['lt' => $utcDateTime]);
        $users = $userCollection->getItems();
        foreach ($users as $user) {
            /** @var User $user */
            $this->disableUserAccount($user);
        }
    }

    private function disableUserAccount(User $user): void
    {
        $user->setData('status', 0);
        $username = $user->getData('username');
        try {
            $this->userResource->save($user);
            $this->logger->info("Account for user $username has been disabled.");
        } catch (\Exception $e) {
            $this->logger->critical(
                __METHOD__ . ": Could not disable account for user $username :" . $e->getMessage(),
                ['exception' => $e]
            );
        }
    }
}
