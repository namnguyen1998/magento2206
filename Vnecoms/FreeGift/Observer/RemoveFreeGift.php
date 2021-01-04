<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Cart as CustomerCart;

class RemoveFreeGift implements ObserverInterface
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
        /** @var $quoteItem \Magento\Quote\Model\Quote\Item */
        $quoteItem = $observer->getQuoteItem();
        
        $this->removeCatalogFreeGifts($quoteItem);
    }
    
    /**
     * Remove catalog free gift
     * 
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     */
    public function removeCatalogFreeGifts(\Magento\Quote\Model\Quote\Item $quoteItem){
        $freeGiftKeyOpt = $quoteItem->getOptionByCode('has_freegift');
        if(!$freeGiftKeyOpt || !$freeGiftKeyOpt->getValue()) return;
        
        $freeGiftKeyOpt = $quoteItem->getOptionByCode('freegift_key');
        $quote = $quoteItem->getQuote();
         
        foreach($quote->getALlItems() as $item){
            if($item->getId() == $quoteItem->getId()) continue;
        
            $keyOption = $item->getOptionByCode('freegift_key');
            if(
                $keyOption &&
                $keyOption->getValue() &&
                $freeGiftKeyOpt->getValue() == $keyOption->getValue()
            ) {
                $item->isDeleted(true);
                $this->messageManager->addSuccess(__('Invalid free gift "%1" is removed automatically.', $item->getName()));
            }
        
        }
    }
}
