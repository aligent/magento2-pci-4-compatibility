<?php

declare(strict_types=1);

namespace Aligent\Pci4Compatibility\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Modal implements ArgumentInterface
{
    /**
     * Constructor
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
    ) {
    }

    /**
     * Returns the duration in ms after refresh when the session expiry warning modal should appear
     *
     * @return int
     */
    public function getModalPopupTimeout(): int
    {
        // This value is in seconds
        $sessionTimeout = $this->scopeConfig->getValue(
            'admin/security/session_lifetime',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );

        // Then we want the popup to appear one minute before timeout
        return ($sessionTimeout - 60) * 1000;
    }
}
