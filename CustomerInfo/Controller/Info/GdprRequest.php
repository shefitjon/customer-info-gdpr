<?php

namespace Ruroc\CustomerInfo\Controller\Info;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\Session as CustomerSession;

class GdprRequest extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var ManagerInterface
     */
    protected $messageManager;
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $messageManager,
        CustomerSession $customerSession
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        try {
            $customer = $this->customerSession->getCustomer();
            if (!$customer->getId()) {
                throw new \Exception(__('Customer not found.'));
            }

            $customerName = $customer->getName() ?: 'Unknown Customer';
            $customerEmail = $customer->getEmail() ?: 'no-reply@example.com';

            $templateVars = [
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
            ];

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $emailTemplate = $this->scopeConfig->getValue('trans_email/ident_support/email', $storeScope);
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('gdpr_request_email_template')
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars($templateVars)
                ->setFromByScope(['email' => $emailTemplate, 'name' => 'Customer Support'])
                ->addTo('shefitjonbregu@gmail.com')
                ->getTransport();

            $transport->sendMessage();

            $this->messageManager->addSuccessMessage(__('Your data deletion request has been sent successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('There was a problem sending your request: ') . $e->getMessage());
            return $resultJson->setData(['error' => true, 'message' => $e->getMessage()]);
        }

        return $resultJson->setData(['success' => true]);
    }
}
