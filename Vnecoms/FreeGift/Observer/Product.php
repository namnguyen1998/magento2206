<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Observer;

use Magento\Framework\Event\ObserverInterface;

class Product implements ObserverInterface
{
    /**
     * @var \Vnecoms\FreeGift\Helper\Data
     */
    protected $_helper;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    public function __construct(
        \Vnecoms\FreeGift\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ){
        $this->_helper              = $helper;
        $this->_customerSession     = $customerSession;
        $this->_storeManager        = $storeManager;
    }
  
    /**
     * Add multiple vendor order row for each vendor.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(!$this->_helper->isEnabledExtension()) return;
        
        /** @var \Magento\Catalog\Model\Product $product */
       $product = $observer->getProduct();
       
       $websiteId = $this->_storeManager->getStore()->getWebsiteId();
       $customerGroupId = $this->_customerSession->getCustomerGroupId();
       
       $freeProductCollection = $this->_helper->getFreeGiftProductsCollection(
           $product,
           $websiteId,
           $customerGroupId
       );
       
       if($freeProductCollection->count()){
          // $product->setHasOptions(true);
           $product->setData('freegift_products',$freeProductCollection);
       }
    }
}
