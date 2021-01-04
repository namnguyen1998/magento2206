<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Cart as CustomerCart;

class RemoveInvalidFreeGift implements ObserverInterface
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @var \Vnecoms\FreeGift\Helper\Data
     */
    protected $_helper;
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    
    /**
     * 
     * @param \Vnecoms\FreeGift\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Vnecoms\FreeGift\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_helper = $helper;
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->messageManager = $messageManager;
    }
    
    /**
     * Add free gift to shopping cart.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $observer->getQuote();
        
        $this->removeInvalidSalesFreeGifts($quote);
    }
    
   
    /**
     * Remove Sales Free Gifts if it's in valid
     * 
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     */
    public function removeInvalidSalesFreeGifts(\Magento\Quote\Model\Quote $quote){
        if(!$this->_helper->isEnabledExtension()) return;
        
        $address = $quote->isVirtual()?$quote->getBillingAddress():$quote->getShippingAddress();

        $freeGiftProductIds = $this->_helper->getShoppingCartFreeGiftProductIds(
            $address,
            $this->_storeManager->getStore()->getWebsiteId(),
            $this->_customerSession->getCustomerGroupId()
        );

        foreach($quote->getAllItems() as $item){
            $freeGiftSalesOpt = $item->getOptionByCode('freegift_sales');
            if(
                $freeGiftSalesOpt &&
                $freeGiftSalesOpt->getValue()
            ) {
                if(!in_array($item->getProductId(), $freeGiftProductIds)) {
                    $quote->removeItem($item->getId());
                    $this->messageManager->addSuccess(__('Invalid free gift "%1" is removed automatically.', $item->getName()));
                }
            }
        }
    }
}
