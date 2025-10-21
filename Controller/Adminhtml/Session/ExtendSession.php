<?php

declare(strict_types=1);

namespace Aligent\Pci4Compatibility\Controller\Adminhtml\Session;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Controller to extend the admin session via AJAX
 */
class ExtendSession extends Action implements HttpPostActionInterface
{

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param AuthSession $authSession
     * @param FormKeyValidator $formKeyValidator
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        private readonly JsonFactory $resultJsonFactory,
        private readonly AuthSession $authSession,
        private readonly FormKeyValidator $formKeyValidator,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    /**
     * Execute action to extend the admin session
     *
     * @return Json
     */
    public function execute(): Json
    {
        $result = $this->resultJsonFactory->create();

        try {
            // Validate form key for CSRF protection
            if (!$this->formKeyValidator->validate($this->getRequest())) {
                throw new LocalizedException(__('Invalid form key. Please refresh the page.'));
            }

            // Verify the user is still authenticated
            if (!$this->authSession->isLoggedIn()) {
                throw new LocalizedException(__('Session has expired. Please log in again.'));
            }

            // Accessing session data automatically refreshes the session timeout
            // The session lifetime is extended by making this authenticated request
            $this->authSession->prolong();

            $this->logger->info(
                'Admin session extended',
                ['user_id' => $this->authSession->getUser()?->getId()]
            );

            return $result->setData([
                'success' => true,
                'message' => __('Your session has been extended.')
            ]);
        } catch (LocalizedException $e) {
            $this->logger->warning('Failed to extend admin session: ' . $e->getMessage());
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error extending admin session: ' . $e->getMessage());
            return $result->setData([
                'success' => false,
                'message' => __('An error occurred while extending your session. Please try again.')
            ]);
        }
    }
}
