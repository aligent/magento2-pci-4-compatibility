<?php

declare(strict_types=1);
namespace Aligent\Pci4Compatibility\Test\Integration;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\TestFramework\ObjectManager;
use Magento\User\Model\ResourceModel\User as UserResource;
use Magento\User\Model\UserFactory;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 */
class PasswordValidationTest extends TestCase
{

    private const VALID_PASSWORD = 'password1234';
    private const INVALID_PASSWORD = 'password123';

    private ?UserFactory $userFactory = null;
    private ?UserResource $userResource = null;

    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = ObjectManager::getInstance();
        $this->userFactory = $objectManager->create(UserFactory::class);
        $this->userResource = $objectManager->create(UserResource::class);
    }

    /**
     * Test that a user can be created with a password of 12 characters
     *
     * @return void
     * @throws AlreadyExistsException
     */
    public function testValidPasswordLength()
    {
        $user = $this->userFactory->create();
        $user->setData(
            [
                'firstname' => 'Admin',
                'lastname' => 'User',
                'username' => 'adminUserWithValidPassword',
                'password' => self::VALID_PASSWORD,
                'email' => 'adminUserWithValidPassword@example.com',
                'role_id' => 1,
                'is_active' => 1
            ]
        );
        $this->userResource->save($user);
        $this->assertNotEmpty($user->getId());
    }

    /**
     * Test that a user cannot be created with a password of less than 12 characters
     *
     * @return void
     * @throws AlreadyExistsException
     */
    public function testInvalidPasswordLength()
    {
        $user = $this->userFactory->create();
        $user->setData(
            [
                'firstname' => 'Admin',
                'lastname' => 'User',
                'username' => 'adminUserWithValidPassword',
                'password' => self::INVALID_PASSWORD,
                'email' => 'adminUserWithValidPassword@example.com',
                'role_id' => 1,
                'is_active' => 1
            ]
        );
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Your password must be at least 12 characters.');
        $this->userResource->save($user);
    }
}
