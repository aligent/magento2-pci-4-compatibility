<?php
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\User;
use Magento\User\Model\ResourceModel\User as UserResource;

$objectManager = Bootstrap::getObjectManager();

$userData = [
    'firstname' => 'Inactive',
    'lastname' => 'Admin1',
    'username' => 'inactiveAdmin1',
    'password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
    'email' => 'inactiveUser1@example.com',
    'role_id' => 1,
    'is_active' => 1,
    'logdate' => date('Y-m-d H:i:s', strtotime('-90 days -1 hour')),
];

$model = $objectManager->create(User::class);
$model->setData($userData);
$userResource = $objectManager->create(UserResource::class);
$userResource->save($model);
