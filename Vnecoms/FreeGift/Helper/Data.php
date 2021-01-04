<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\CatalogRule\Model\RuleFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Vnecoms\FreeGift\Model\ResourceModel\CatalogRule\CollectionFactory;
use Vnecoms\FreeGift\Model\CatalogRule;
use Magento\Framework\App\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Data extends AbstractHelper
{
    const XML_PATH_ENABLED          = 'freegift/general/enabled';
    const XML_NO_OF_FREEGIFT        = 'freegift/sales_config/no_of_freegift';
    const XML_PATH_DISPLAY_PAGES    = 'freegift/sales_config/display_pages';
    
    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;
    
    /**
     * @var ProductCollectionFactory
     */
    protected $_productCollectionFactory;
    
    /**
     * @var RuleFactory
     */
    protected $_ruleFactory;
    
    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;
    
    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;
    
    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * @var \Vnecoms\FreeGift\Model\SalesRuleFactory
     */
    protected $salesRuleFactory;

    /**
     * @var \Vnecoms\FreeGift\Model\CatalogRuleFactory
     */
    protected $catalogRuleFactory;
    
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        RuleFactory $ruleFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Vnecoms\FreeGift\Model\CatalogRuleFactory $catalogRuleFactory,
        \Vnecoms\FreeGift\Model\SalesRuleFactory $salesRuleFactory,
        \Magento\Catalog\Model\Config $catalogConfig,
        DateTime $dateTime
    ) {
        $this->_collectionFactory           = $collectionFactory;
        $this->_productCollectionFactory    = $productCollectionFactory;
        $this->_ruleFactory                 = $ruleFactory;
        $this->_dateTime                    = $dateTime;
        $this->productVisibility            = $productVisibility;
        $this->catalogConfig                = $catalogConfig;
        $this->catalogRuleFactory           = $catalogRuleFactory;
        $this->salesRuleFactory             = $salesRuleFactory;
        
        parent::__construct($context);
    }
    
    /**
     * Is enabled extension
     * 
     * @return boolean
     */
    public function isEnabledExtension(){
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_ENABLED);
    }
    
    /**
     * Get number of free gift
     * 
     * @return number
     */
    public function getNumberOfFreeGift(){
        return (int) $this->scopeConfig->getValue(self::XML_NO_OF_FREEGIFT);
    }
    
    /**
     * Get Display Pages
     * 
     * @return multitype:
     */
    public function getDisplayPages(){
        return explode(',', $this->scopeConfig->getValue(self::XML_PATH_DISPLAY_PAGES));
    }
    
    /**
     * Get Free Gift Products
     * @param \Magento\Catalog\Model\Product $productId
     * @param int $websiteId
     * @param int $customerGroupId
     * @return void|multitype:
     */
    public function getFreeGiftProductIds(
        \Magento\Catalog\Model\Product $product,
        $websiteId,
        $customerGroupId,
        &$appliedRuleId = 0
    ) {
        /** @var \Vnecoms\FreeGift\Model\ResourceModel\CatalogRule\Collection $ruleCollection */
        $ruleCollection = $this->_collectionFactory->create()
            ->addFieldToFilter('website_ids',['finset' => $websiteId])
			->addFieldToFilter('group_ids',['finset'=>$customerGroupId])
            ->addFieldToFilter('is_active',CatalogRule::STATUS_ENABLED);
        
        $now = $this->_dateTime->date('Y-m-d');
        $ruleCollection->getSelect()->where('from_date is null or from_date <= ?', $now)
                ->where('to_date is null or to_date >= ?', $now)
                ->order('sort_order ASC');
        
        
        $freeProductIds = [];
        if($ruleCollection->count()){
            foreach($ruleCollection as $rule){
                /** @var \Magento\CatalogRule\Model\Rule $tmpRule */
                $tmpRule = $this->_ruleFactory->create();
                $tmpRule->setConditionsSerialized($rule->getConditionsSerialized());
                
                if($tmpRule->getConditions()->validate($product)){
                    $appliedRuleId = $rule->getId();
                    $freeProductIds = array_merge($freeProductIds, explode(",", $rule->getProductIds()));
                    if($rule->getStopRulesProcessing()) break;
                }
            }
        }
        $product->setAppliedFreeGiftRuleId($appliedRuleId);

        return $freeProductIds;
    }
    
    /**
     * Get Free Products
     * 
     * @param \Magento\Catalog\Model\Product $product
     * @param int $websiteId
     * @param int $customerGroupId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getFreeGiftProductsCollection(
        \Magento\Catalog\Model\Product $product,
        $websiteId,
        $customerGroupId,
        &$appliedRuleId = 0
    ) {
        $productIds = $this->getFreeGiftProductIds(
            $product, 
            $websiteId, 
            $customerGroupId,
            $appliedRuleId
        );
        
        $collection = $this->_productCollectionFactory->create()
            ->addAttributeToFilter('entity_id',['in' => $productIds])
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->setVisibility($this->productVisibility->getVisibleInCatalogIds());
        
        return $collection;
    }
    
    /**
     * Get shopping cart free gift products
     * 
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param unknown $websiteId
     * @param unknown $customerGroupId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getShoppingCartFreeGiftProducts(
        \Magento\Quote\Model\Quote\Address $address,
        $websiteId,
        $customerGroupId
    ) {
        $productIds = $this->getShoppingCartFreeGiftProductIds($address, $websiteId, $customerGroupId);
        
        $collection = $this->_productCollectionFactory->create()
            ->addAttributeToFilter('entity_id',['in' => $productIds])
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->setVisibility($this->productVisibility->getVisibleInCatalogIds());
        
        return $collection;
    }
    
    /**
     * Get Shopping cart free gift product ids
     * 
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param unknown $websiteId
     * @param unknown $customerGroupId
     * @return multitype:
     */
    public function getShoppingCartFreeGiftProductIds(
        \Magento\Quote\Model\Quote\Address $address,
        $websiteId,
        $customerGroupId
    ) {
        $address->setData('total_qty', $this->getTotalQty($address));
        
        $rulesCollection = $this->getActiveSalesRules($websiteId, $customerGroupId);
        $productIds = [];
        foreach($rulesCollection as $rule){
            $ruleFactory = ObjectManager::getInstance()->create('Magento\SalesRule\Model\RuleFactory');
            /** @var \SalesRule\CatalogRule\Model\Rule $tmpRule*/
            $tmpRule = $ruleFactory->create();
            $tmpRule->setConditionsSerialized($rule->getConditionsSerialized());
        
            if($tmpRule->getConditions()->validate($address)){
                $appliedRuleId = $rule->getId();
                $address->setAppliedFreeGiftRule($appliedRuleId);
                $productIds = array_merge($productIds, explode(",", $rule->getProductIds()));
                if($rule->getStopRulesProcessing()) break;
            }
        }
        
        return $productIds;
    }
    
    /**
     * Get total items qty (exclude freegift item).
     * 
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return number
     */
    public function getTotalQty(\Magento\Quote\Model\Quote\Address $address){
        $freeCount = 0;
        $count = 0;
        foreach($address->getQuote()->getAllVisibleItems() as $item){
            if($item->isDeleted()) continue;
            $count += $item->getQty();
            $freeGiftSalesOpt = $item->getOptionByCode('freegift_sales');
            $freeGiftCatalogOpt = $item->getOptionByCode('freegift_catalog');
            if(
                ($freeGiftSalesOpt && $freeGiftSalesOpt->getValue()) ||
                ($freeGiftCatalogOpt && $freeGiftCatalogOpt->getValue())
            ) {
                $freeCount +=$item->getQty();
            }
        }
        return $count - $freeCount;
    }
    
    /**
     * Get active sales rules collection
     * 
     * @param int $websiteId
     * @param int $customerGroupId
     * @return \Vnecoms\FreeGift\Model\ResourceModel\SalesRule\Collection
     */
    public function getActiveSalesRules($websiteId, $customerGroupId){
        $ruleCollection = ObjectManager::getInstance()
            ->create('Vnecoms\FreeGift\Model\ResourceModel\SalesRule\Collection')
            ->addWebsiteGroupDateFilter($websiteId, $customerGroupId)
            ->addIsActiveFilter()
            ->setOrder('sort_order','ASC');
        return $ruleCollection;
    }

// // // // // Custom // // // // //

    /**
     * Get number of free gifts that allows customer to select
     * @return Ambigous <number, unknown>
     */
    public function getNumberOfCatalogFreeGift($appliedRuleId){
        $rule = $this->catalogRuleFactory->create()->load($appliedRuleId);
        $noOfFreegift = 0;
        if ($rule->getSimpleAction() == \Vnecoms\FreeGift\Model\CatalogRule::ACTION_SELECT)
            $noOfFreegift = $rule->getNoOfFreegift();
        return $noOfFreegift;
    }

    public function getNumberOfSalesFreeGift($appliedRuleId){
        $rule = $this->salesRuleFactory->create()->load($appliedRuleId);
        return $rule->getNoOfFreegift();
    }

}
