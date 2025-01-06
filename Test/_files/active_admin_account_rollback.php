<?php
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\ResourceModel\User as UserResource;
use Magento\User\Model\User;

$objectManager = Bootstrap::getObjectManager();

$user = $objectManager->create(User::class);
$userResource = $objectManager->create(UserResource::class);

$user->loadByUsername('activeAdmin1');
if ($user->getId()) {
    $userResource->delete($user);;
}
