<?php

declare(strict_types=1);

namespace Aligent\Pci4Compatibility\ViewModel;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Modal implements ArgumentInterface
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $backendUrl
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly UrlInterface $backendUrl,
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

    /**
     * Get the session lifetime in seconds
     *
     * @return int
     */
    public function getSessionLifetime(): int
    {
        return (int) $this->scopeConfig->getValue(
            'admin/security/session_lifetime',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    /**
     * Get URL for extending admin session
     *
     * @return string
     */
    public function getExtendSessionUrl(): string
    {
        return $this->backendUrl->getUrl('pci4/session/extendsession');
    }
}
