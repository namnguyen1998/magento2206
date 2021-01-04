<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Cart as CustomerCart;

class Cart implements ObserverInterface
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

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Vnecoms\FreeGift\Model\CatalogRuleFactory
     */
    protected $_ruleFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockRegistry;

    public function __construct(
        \Vnecoms\FreeGift\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CustomerCart $cart,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Vnecoms\FreeGift\Model\CatalogRuleFactory $ruleFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ){
        $this->_helper              = $helper;
        $this->_customerSession     = $customerSession;
        $this->_storeManager        = $storeManager;
        $this->_cart                = $cart;
        $this->_ruleFactory         = $ruleFactory;
        $this->_stockRegistry       = $stockRegistry; 
        $this->messageManager       = $messageManager;
    }

    /**
     * Add free gift to shopping cart.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(!$this->_helper->isEnabledExtension()) return;

        /** @var \Magento\Quote\Model\Quote\Item */
        $item = $observer->getQuoteItem();
        /** @var \Magento\Catalog\Model\Product */
        $product = $observer->getProduct();


        // if($item->getBuyRequest()->getData('selected_free_gifts') === null) {
        //     return;
        // }

		/* The item is shopping cart free gift*/
        if(
			$item->getOptionByCode('freegift_sales') ||
			$item->getOptionByCode('freegift_catalog')
		){
			// $item->setCustomPrice(0);
            // $item->setOriginalCustomPrice(0);
            $item->setCustomPrice(0);
			$item->setOriginalCustomPrice(0);
			return;
		}
		
        $freegiftKeyOpt = $item->getOptionByCode('freegift_key');
        if($freegiftKeyOpt) return;

        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $customerGroupId = $this->_customerSession->getCustomerGroupId();
        $appliedRuleId = 0;
        $freeProducts = $this->_helper->getFreeGiftProductsCollection(
            $product,
            $websiteId,
            $customerGroupId,
            $appliedRuleId
        );

        if(!$freeProducts->count()) {
            $item->addOption(['code'=> 'has_freegift', 'value' =>0 ,'product_id'=>$product->getId()]);
            return;
        }

        /*In the default, selected free gift is all available free gift. This value will be changed if the action of rule is SELECT mode.*/
        $selectedFreeGift = $freeProducts->getAllIds();

        /*If the rule is select mode make sure all selected products is valid*/
        $rule = $this->_ruleFactory->create()->load($appliedRuleId);
        if($rule->getSimpleAction() == \Vnecoms\FreeGift\Model\CatalogRule::ACTION_SELECT){
            $buyRequest = $item->getBuyRequest();

            $selectedFreeGift = trim($buyRequest->getData('selected_free_gifts'));
            if(!$selectedFreeGift){
                $item->addOption(['code'=> 'has_freegift', 'value' =>0,'product_id'=>$product->getId()]);
                return;
            }

            $availableGiftIds = $freeProducts->getAllIds();
            $selectedFreeGift = explode(",", $selectedFreeGift);
            $errMessage = __('Selected free gift(s) are not valid. Please try again.');

            if(sizeof($selectedFreeGift) > $rule->getNoOfFreegift()){
                $this->messageManager->addError($errMessage);
                throw new \Exception($errMessage);
            }
            foreach($selectedFreeGift as $productId){
                /*If the selected gift is not in available gift just throw error*/
                if(!in_array($productId, $selectedFreeGift)){
                    $this->messageManager->addError($errMessage);
                    throw new \Exception($errMessage);
                }
            }
        }


        $key = md5(md5($product->getId()).rand(0, 999).time());
        $item->addOption(['code'=> 'freegift_key', 'value' =>$key,'product_id'=>$product->getId()]);
        $item->addOption(['code'=> 'has_freegift', 'value' =>1,'product_id'=>$product->getId()]);

        foreach($freeProducts as $gift){
            $productStock = $this->_stockRegistry->getStockItem($gift->getId(), $gift->getStore()->getWebsiteId());  
            if ($productStock->getIsInStock()) {
                /*Don't add the free gift that is not in selected free gifts.*/
                if(!in_array($gift->getId(), $selectedFreeGift)) continue;

                $gift->addCustomOption('freegift_catalog', 1);
                $gift->addCustomOption('freegift_key', $key);

                $type = 'freegift';
                switch($gift->getTypeId()){
                    case 'configurable':
                        $type = 'freegift_configurable';
                        break;
                }

                $gift->addCustomOption('product_type', $type);
                $this->_cart->addProduct(
                    $gift,
                    [
                        'freegift_key' => $key,
                        'qty' => $product->getQty(),
                    ]
                );

                $message = __(
                    'Free Gift %1 is added to your shopping cart.',
                    $gift->getName()
                );
                $this->messageManager->addSuccessMessage($message);
            }
        }
    }
}
