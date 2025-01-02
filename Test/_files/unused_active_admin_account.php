<?php
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\ResourceModel\User as UserResource;
use Magento\User\Model\User;

$objectManager = Bootstrap::getObjectManager();

$userData = [
    'firstname' => 'Active',
    'lastname' => 'Admin2',
    'username' => 'activeAdmin2',
    'password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
    'email' => 'activeUser2@example.com',
    'role_id' => 1,
    'is_active' => 1,
    'created' => date('Y-m-d H:i:s', strtotime('-89 days')),
    'logdate' => null,
];

$model = $objectManager->create(User::class);
$model->setData($userData);
$userResource = $objectManager->create(UserResource::class);
$userResource->save($model);
