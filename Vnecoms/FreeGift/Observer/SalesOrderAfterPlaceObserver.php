<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SalesOrderAfterPlaceObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected $quoteFactory;
    protected $ruleCustomerFactory;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Vnecoms\FreeGift\Model\CustomerFactory $ruleCustomerFactory
    ) {
        $this->objectManager = $objectManager;
        $this->quoteFactory = $quoteFactory;
        $this->ruleCustomerFactory = $ruleCustomerFactory;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order) {
            return $this;
        }

        $ruleCustomer = null;
        $customerId = $order->getCustomerId();

        if (!$customerId) {
            return $this;
        }

        $quote = $this->quoteFactory->create()->load($order->getQuoteId());

        foreach ($quote->getAllVisibleItems() as $item) {
            if ($itemOption = $item->getProduct()->getCustomOption('freegift_sales_rule')) {
                $ruleId = $itemOption->getValue();
                $ruleCustomer = $this->ruleCustomerFactory->create();
                $ruleCustomer->loadByCustomerRule($customerId, $ruleId);

                if ($ruleCustomer->getId()) {
                    $ruleCustomer->setTimesUsed($ruleCustomer->getTimesUsed() + 1);
                } 
                else {
                    $ruleCustomer->setCustomerId($customerId)->setRuleId($ruleId)->setTimesUsed(1);
                }
                $ruleCustomer->save();
                break;
            }
        }

        return $this;
    }
}
