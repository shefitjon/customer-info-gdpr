<?php

namespace Ruroc\CustomerInfo\Block\Customer;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Helper\View;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;


class Info extends Template
{
    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;
    /**
     * @var View
     */
    protected $_helperView;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;
    /**
     * @var PriceHelper
     */
    protected $_priceHelper;

    public function __construct(
        Context $context,
        CurrentCustomer $currentCustomer,
        View $helperView,
        OrderRepositoryInterface $orderRepository,
        OrderCollectionFactory $orderCollectionFactory,
        PriceHelper $_priceHelper,
        array $data = []
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->_helperView = $helperView;
        $this->orderRepository = $orderRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->_priceHelper = $_priceHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    public function getCustomer()
    {
        try {
            return $this->currentCustomer->getCustomer();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @return array|string
     */
    public function getName()
    {
        $customer = $this->getCustomer();
        if ($customer) {
            return $this->_helperView->getCustomerName($customer);
        }
        return __('Guest');
    }

    /**
     * @return array|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getCustomerOrders()
    {
        $customerId = $this->getCustomer()->getId();
        if ($customerId) {
            return $this->orderCollectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('customer_id', $customerId)
                ->setOrder('created_at', 'desc');
        }
        return [];
    }

    /**
     * @param $amount
     * @return float|string
     */
    public function getPriceFormat($amount)
    {
        return $this->_priceHelper->currency($amount, true, false);
    }
}
