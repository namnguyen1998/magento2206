<?php
namespace Vnecoms\FreeGift\Block;

/**
 * Class View
 * @package Vnecoms\FreeGift\Block\Product
 */
class Product extends \Magento\Catalog\Block\Product\AbstractProduct
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
     * @var \Vnecoms\FreeGift\Model\CatalogRuleFactory
     */
    protected $_catalogRuleFactory;
    
    /**
     * @var \Vnecoms\FreeGift\Model\CatalogRule
     */
    protected $_appliedRule;
    
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_freeProducts;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockRegistry;
    
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Vnecoms\FreeGift\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession,
        \Vnecoms\FreeGift\Model\CatalogRuleFactory $catalogRuleFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        array $data = []
    ) {       
        $this->_helper              = $helper;
        $this->_customerSession     = $customerSession;
        $this->_catalogRuleFactory  = $catalogRuleFactory;   
        $this->_stockRegistry       = $stockRegistry;   
        parent::__construct($context, $data);
    }

    public function getStockItem($productId, $websiteId)
    {
        return $this->_stockRegistry->getStockItem($productId, $websiteId);
    }
    
    /**
     * Retrieve current product model
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->_coreRegistry->registry('product') && $this->getProductId()) {
            $product = $this->productRepository->getById($this->getProductId());
            $this->_coreRegistry->register('product', $product);
        }
        return $this->_coreRegistry->registry('product');
    }
    
    /**
     * Get Free Product Ids
     * 
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getFreeProducts(){
        if(!$this->_freeProducts){
            $freegiftProducts = $this->getProduct()->getFreegiftProducts();
            if(
                $freegiftProducts &&
                ($freegiftProducts instanceof \Magento\Catalog\Model\ResourceModel\Product\Collection)
            ) {
                $this->_freeProducts = $freegiftProducts;
            }else{
                $websiteId = $this->_storeManager->getStore()->getWebsiteId();
                $customerGroupId = $this->_customerSession->getCustomerGroupId();
                
                $this->_freeProducts =  $this->_helper->getFreeGiftProductsCollection(
                    $this->getProduct(),
                    $websiteId,
                    $customerGroupId
                );
            }
        }
        return $this->_freeProducts;
    }
    
    /**
     * Get Product Image URL
     * 
     * @param \Magento\Catalog\Model\Product $product
     * @param number $width
     * @param number $height
     * @return string
     */
    public function getProductImageUrl(
        \Magento\Catalog\Model\Product $product,
        $width = 80,
        $height = 80
    ) {
        $helper = $this->_imageHelper->init($product, 'category_page_grid')->resize($width, $height);
        return $helper->getUrl();
    }
    
    /**
     * Get Applied Rule
     * 
     * @return \Vnecoms\FreeGift\Model\CatalogRule
     */
    public function getAppliedRule(){
        if(!$this->_appliedRule){
            $this->_appliedRule = $this->_catalogRuleFactory->create();
            $this->_appliedRule->load($this->getProduct()->getAppliedFreeGiftRuleId());
        }
        
        return $this->_appliedRule;
    }
    
    /**
     * Is Select mode
     * 
     * @return boolean
     */
    public function isSelectMode(){
        return $this->getAppliedRule()->getSimpleAction() == \Vnecoms\FreeGift\Model\CatalogRule::ACTION_SELECT;
    }
    
    /**
     * Get number of free gifts that allows customer to select
     * @return Ambigous <number, unknown>
     */
    public function getNumberOfFreeGift(){
        $noOfFreegift = $this->getAppliedRule()->getNoOfFreegift();
        return $noOfFreegift?$noOfFreegift:0;
    }
    
    
    /**
     * disable this block if there is no free gift
     *
     * @see \Magento\Framework\View\Element\Template::_toHtml()
     */
    protected function _toHtml(){
        if(!$this->_helper->isEnabledExtension()) return '';
        
        if(!sizeof($this->getFreeProducts())) return '';
        return parent::_toHtml();
    }
}
