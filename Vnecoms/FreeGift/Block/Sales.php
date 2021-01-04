<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Block;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Checkout\Model\Cart as CustomerCart;

/**
 * Sales order history block
 */
class Sales extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    
    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;
    
    /**
     * @var Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;
    
    /**
     * @var array
     */
    protected $jsLayout;
    
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;
    
    /**
     * @var \Vnecoms\FreeGift\Helper\Data
     */
    protected $_helper;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @var \Vnecoms\FreeGift\Model\SalesRuleFactory
     */
    protected $salesRuleFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockRegistry;
    protected $_imageBuilder;
    
    /**
     * 
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param CustomerCart $cart
     * @param \Vnecoms\FreeGift\Helper\Data $helper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
	\Magento\Catalog\Block\Product\ImageBuilder $_imageBuilder,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        CustomerCart $cart,
        \Vnecoms\FreeGift\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession,
        \Vnecoms\FreeGift\Model\SalesRuleFactory $salesRuleFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $context->getRegistry();
	$this->_imageBuilder = $_imageBuilder;
        $this->_date = $date;
        $this->_localeFormat = $localeFormat;
        $this->priceCurrency = $priceCurrency;
        $this->_jsonEncoder = $jsonEncoder;
        $this->cart = $cart;
        $this->_helper = $helper;
        $this->_customerSession = $customerSession;
        $this->salesRuleFactory = $salesRuleFactory;
        $this->_stockRegistry   = $stockRegistry; 
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        return parent::__construct($context, $data);
    }
    
    /**
     * @return string
     */
     public function getImage($product, $imageId, $attributes = [])
    {
        return $this->_imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }

    public function getJsLayout()
    {
        $products = [];
        $numberOfFreeGift = $this->getNumberOfAddedFreeGifts();
        
        $ruleData = $this->getFreeGiftRuleData();
        $appliedRule = $ruleData['applied_rule'];
        $maxNumberOfFreeGift = $appliedRule->getNoOfFreegift();
        /* Check if the customer add a freegift to shopping cart already*/
        if($numberOfFreeGift < $maxNumberOfFreeGift){
            $productCollection = $ruleData['product_collection'];
            foreach($productCollection as $product){
                $productStock = $this->_stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
                if ($productStock->getIsInStock()) {       
                    $productData = $product->getData();
                    $productData['name'] = $this->stripTags($product->getName(), null, true);
                    //$productData['image_html'] = $this->getImage($product, 'category_page_grid')->toHtml();
                    $image = $this->getImage($product, 'product_small_image');
                    $productData['image_html'] = $image->getImageUrl();
                    $productData['product_url'] = $product->getProductUrl();
                    $products[] = $productData;
                }
            }
        }
        
        $this->jsLayout['components']['freegift']['products'] = $products;
        $this->jsLayout['components']['freegift']['addProductUrl'] = $this->getUrl('freegift/product/add');
        $this->jsLayout['components']['freegift']['priceFormat'] = $this->getPriceFormat();
        $this->jsLayout['components']['freegift']['basePriceFormat'] = $this->getBasePriceFormat();
        $this->jsLayout['components']['freegift']['exchangeRate'] = $this->priceCurrency->convert(1);
        $this->jsLayout['components']['freegift']['current_page'] = $this->getRequest()->getFullActionName();
        $this->jsLayout['components']['freegift']['freegift_limit'] = $maxNumberOfFreeGift;
        $this->jsLayout['components']['freegift']['added_freegift_count'] = $numberOfFreeGift;
        return \Zend_Json::encode($this->jsLayout);
    }
    
    /**
     * Get product collection
     * 
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getFreeGiftRuleData(){
        $quote = $this->cart->getQuote();
        $address = $quote->isVirtual()?
            $quote->getBillingAddress():
            $quote->getShippingAddress();

        $this->setData(
            'freegift_product_collection',
            $this->_helper->getShoppingCartFreeGiftProducts(
                $address,
                $this->_storeManager->getStore()->getWebsiteId(),
                $this->_customerSession->getCustomerGroupId()
            )
        );
        
        $appliedRule = $this->salesRuleFactory->create();
        $appliedRule->load($address->getAppliedFreeGiftRule());
        $this->setData('freegift_salesrule', $appliedRule);
        
        return [
            'product_collection' => $this->getData('freegift_product_collection'),
            'applied_rule' => $this->getData('freegift_salesrule'),
        ];
    }
    
    /**
     * get number of added free gifts
     * 
     * @return number
     */
    public function getNumberOfAddedFreeGifts(){
        $count = 0;
        foreach($this->cart->getItems() as $item){
            $freeGiftSalesOpt = $item->getOptionByCode('freegift_sales');
            if(
                $freeGiftSalesOpt &&
                $freeGiftSalesOpt->getValue()
            ) {
                $count +=$item->getQty();
            }
        }
        
        return $count;
    }
    
    /**
     * Get price format json.
     *
     * @return string
     */
    public function getPriceFormat()
    {
        return $this->_localeFormat->getPriceFormat();
    }
    
    /**
     * Get price format json.
     *
     * @return string
     */
    public function getBasePriceFormat()
    {
        return $this->_localeFormat->getPriceFormat(null, $this->_storeManager->getStore()->getBaseCurrencyCode());
    }
    
    protected function _toHtml(){
        if(!$this->_helper->isEnabledExtension()) return '';
        
        if(!in_array($this->getRequest()->getFullActionName(), $this->_helper->getDisplayPages())) return '';

        return parent::_toHtml();
    }
}
