<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vnecoms\FreeGift\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Cart source
 */
class FreeGift extends \Magento\Framework\DataObject implements SectionSourceInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Model\Quote|null
     */
    protected $quote = null;

    /**
     * @var \Vnecoms\FreeGift\Helper\Data
     */
    protected $_helper;
    
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_productCollection;
    
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
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    protected $imageBuilder;
    
    /**
     * @var \Vnecoms\FreeGift\Model\SalesRuleFactory
     */
    protected $salesRuleFactory;
    
    /**
     * @var \Vnecoms\FreeGift\Model\SalesRule
     */
    protected $appliedRule;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockRegistry;
    
    /**
     * 
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Vnecoms\FreeGift\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\View\LayoutInterface $layout,
        \Vnecoms\FreeGift\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Vnecoms\FreeGift\Model\SalesRuleFactory $salesRuleFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        array $data = []
    ) {
        parent::__construct($data);
        $this->checkoutSession = $checkoutSession;
        $this->layout = $layout;
        $this->_helper = $helper;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->imageBuilder = $imageBuilder;
        $this->salesRuleFactory = $salesRuleFactory;
        $this->_stockRegistry   = $stockRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        if(!$this->_helper->isEnabledExtension()) return [
            'products' => [],
            'freegift_limit' => 0,
            'added_freegift_count' => 0,
        ];
        
        $products = [];
        
        $count = 0;
        foreach($this->getQuote()->getAllItems() as $item){
            $freeGiftSalesOpt = $item->getOptionByCode('freegift_sales');
            if(
                $freeGiftSalesOpt &&
                $freeGiftSalesOpt->getValue()
            ) {
                $count +=$item->getQty();
            }
        }
        
        $ruleData = $this->getFreeGiftRuleData();
        $productCollection = $ruleData['product_collection'];
        foreach($productCollection as $product){
            $productStock = $this->_stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
            if ($productStock->getIsInStock()) { 
                $productData = $product->getData();
                $productData['image_html'] = $this->getImage($product, 'category_page_grid')->toHtml();
                $productData['product_url'] = $product->getProductUrl();
                $products[] = $productData;
            }
        }
        
        $appliedRule = $ruleData['applied_rule'];
        $maxNumberOfFreeGift = $appliedRule->getNoOfFreegift();
        if($count >= $maxNumberOfFreeGift) return [
            'products' => [],
            'freegift_limit' => $maxNumberOfFreeGift,
            'added_freegift_count' => $count,
        ];
        
        return [
            'products' => $products,
            'freegift_limit' => $maxNumberOfFreeGift,
            'added_freegift_count' => $count,
        ];
    }

    /**
     * Get active quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }
    
    /**
     * Get Freegift Rule Data
     * 
     * @return multitype:\Magento\Catalog\Model\ResourceModel\Product\Collection \Vnecoms\FreeGift\Model\SalesRule
     */
    public function getFreeGiftRuleData(){
        if(!$this->_productCollection){
            $address = $this->getQuote()->isVirtual()?
                $this->getQuote()->getBillingAddress():
                $this->getQuote()->getShippingAddress();
            
            $this->_productCollection = $this->_helper->getShoppingCartFreeGiftProducts(
                $address,
                $this->_storeManager->getStore()->getWebsiteId(),
                $this->_customerSession->getCustomerGroupId()
            );
            
            $appliedRule = $this->salesRuleFactory->create();
            $appliedRule->load($address->getAppliedFreeGiftRule());
            $this->appliedRule = $appliedRule;
            
        }
    
        return [
            'product_collection' => $this->_productCollection,
            'applied_rule' => $this->appliedRule,
        ];
    }

    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }
}
