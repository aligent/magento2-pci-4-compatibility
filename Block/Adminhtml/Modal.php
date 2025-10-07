<?php

declare(strict_types=1);

namespace Aligent\Pci4Compatibility\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Modal extends Template
{
    /**
     * Constructor
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Returns the time in ms after refresh when the session expiry warning modal should appear
     *
     * @return int
     */
    public function getModalPopupTimeout(): int
    {
        // Session lifetime in seconds
        $sessionTimeout = $this->_scopeConfig->getValue(
            'admin/security/session_lifetime',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );

        // We want the popup to appear one minute before the timeout (in ms)
        return ($sessionTimeout - 60) * 1000;
    }
}
