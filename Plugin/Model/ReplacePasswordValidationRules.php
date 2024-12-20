<?php

declare(strict_types=1);
namespace Aligent\Pci4Compatibility\Plugin\Model;

use Magento\Framework\Validator\DataObject;
use Magento\Framework\Validator\NotEmpty;
use Magento\Framework\Validator\Regex;
use Magento\Framework\Validator\StringLength;
use Magento\User\Model\UserValidationRules;

class ReplacePasswordValidationRules
{

    private const MIN_PASSWORD_LENGTH = 12;

    /**
     * Updates the requirements for admin passwords so that the minimum length is 12 characters
     *
     * @param UserValidationRules $subject
     * @param callable $proceed
     * @param DataObject $validator
     * @return DataObject
     */
    public function aroundAddPasswordRules(
        UserValidationRules $subject,
        callable $proceed,
        DataObject $validator
    ): DataObject {
        $passwordNotEmpty = new NotEmpty();
        $passwordNotEmpty->setMessage(__('Password is required field.'), NotEmpty::IS_EMPTY);
        $passwordLength = new StringLength(['min' => self::MIN_PASSWORD_LENGTH, 'encoding' => 'UTF-8']);
        $passwordLength->setMessage(
            __('Your password must be at least %1 characters.', self::MIN_PASSWORD_LENGTH),
            StringLength::TOO_SHORT
        );
        $passwordChars = new Regex('/[a-z].*\d|\d.*[a-z]/iu');
        $passwordChars->setMessage(
            __('Your password must include both numeric and alphabetic characters.'),
            Regex::NOT_MATCH
        );
        $validator->addRule($passwordNotEmpty,'password');
        $validator->addRule($passwordLength, 'password');
        $validator->addRule($passwordChars,'password');

        return $validator;
    }
}
