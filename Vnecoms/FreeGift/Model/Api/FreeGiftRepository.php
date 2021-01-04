<?php

namespace Vnecoms\FreeGift\Model\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

use Magento\Customer\Model\CustomerFactory;

use Magento\CatalogInventory\Api\StockRegistryInterface;

use Magento\Quote\Api\CartRepositoryInterface;

use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\QuoteRepository\SaveHandler;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

use Vnecoms\FreeGift\Api\FreeGiftRepositoryInterface;
use Vnecoms\FreeGift\Model\Api\Data\CatalogRules;
use Vnecoms\FreeGift\Helper\Data;

/**
 * Class FreeGiftRepositoryInterface
 * @package Vnecoms\FreeGift\Model\Api
 */
class FreeGiftRepository implements FreeGiftRepositoryInterface
{
    /**
     * 
     * @var Vnecoms\FreeGift\Helper\Data
     */
    protected $helperData;

    /**
     * 
     * @var Vnecoms\FreeGift\Model\Api\Data\CatalogRules
     */
    protected $catalogRules;

    /**
     * Magento\Catalog\Api\Data\ProductInterface
     * @var ProductInterface
     */
    protected $productInterface;

    /**
     * Magento\Catalog\Model\ProductFactory
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var CustomerFactory
     */
    protected $customer;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepositoryInterface;

    /**
     * @var CartItemInterfaceFactory
     */
    protected $cartitem;

    /**
     * @var Item
     */
    protected $item;

    /**
     * @var Quote
     */
    protected $quote;

    /** 

     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;
       
    /**
     * @var SaveHandler
     */
    protected $saveRepository;

    /**
     * @var ItemFactory
     */
    protected $quoteItemFactory;


    public function __construct(
        Data $helperData,
        CatalogRules $catalogRules,
        ProductInterface $productInterface,
        ProductFactory $productFactory,
        ProductCollectionFactory $productCollectionFactory,
        CartItemInterfaceFactory $cartitem,
        Item $item,
        Quote $quote,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteFactory $quoteFactory,
        QuoteRepository $quoteRepository,
        SaveHandler $saveRepository,
        ItemFactory $quoteItemFactory,
        CustomerFactory $customer,
        StockRegistryInterface $stockRegistry,
        CartRepositoryInterface $cartRepositoryInterface
        
    ) {
        $this->helperData                   = $helperData;
        $this->catalogRules                 = $catalogRules;
        $this->productInterface             = $productInterface;
        $this->productFactory               = $productFactory;
        $this->productCollectionFactory     = $productCollectionFactory;
        $this->cartitem                     = $cartitem;
        $this->item                         = $item;
        $this->quote                        = $quote;
        $this->quoteIdMaskFactory           = $quoteIdMaskFactory;
        $this->quoteFactory                 = $quoteFactory;
        $this->quoteRepository              = $quoteRepository;
        $this->saveRepository               = $saveRepository;
        $this->quoteItemFactory             = $quoteItemFactory;
        $this->customer                     = $customer;
        $this->stockRegistry                = $stockRegistry;
        $this->cartRepositoryInterface      = $cartRepositoryInterface;
    }

// // // // Catalog Rules // // // //
    public function getProductFGbyProductId($productId)
    {
        $customerGroupId = 0;
        $product = $this->productFactory->create()->load($productId);
        $productIds = $this->helperData->getFreeGiftProductIds($product, $product->getWebsiteIds(), $customerGroupId, $appliedRuleId);
        $collection = $this->productCollectionFactory->create()->addAttributeToSelect('*')
                            ->addAttributeToFilter('entity_id',['in' => $productIds]);

        if ( $collection->getItems() ){
            $selectNumber = $this->helperData->getNumberOfCatalogFreeGift($appliedRuleId);
            $this->catalogRules->setSelect($selectNumber);
            $this->catalogRules->setFreegiftProducts($collection->getItems());
            return $this->catalogRules;
        }
        else 
            return [];
    }

    public function addCartCatalogRules($quoteId, $customerId, $productId, $freeProductIds, $qty)
    {
        // check empty quote id mask
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($quoteId, 'masked_id');
        if ( $quoteIdMask->getData() ){
            $quote = $this->quoteRepository->get($quoteIdMask->getQuoteId());
            if ( $quote->getData() ){
                $customerId = 0;
                return $this->addToCart($quote, $customerId, $productId, $freeProductIds, $qty);
            }
        }
        // check empty quote id
        $quote = $this->quoteFactory->create()->loadByIdWithoutStore($quoteId);
        if ( $quote->getData() ){
            if ( ($quote->getCustomerId() == $customerId) ){
                return $this->addToCart($quote, $customerId, $productId, $freeProductIds, $qty);
            }
        }

        return false;
    }

    protected function addToCart($quote, $customerId, $productId, $freeProductIds, $qty)
    {
        if ( $customerId == 0 )
            $customerGroupId = 0;
        else {
            $customer = $this->customer->create()->load($customerId);
            $customerGroupId = $customer->getGroupId();
        }
        $selectedFreeGift = explode(',', $freeProductIds);
        $product = $this->productFactory->create()->load($productId);
        $productIds = $this->helperData->getFreeGiftProductIds($product, $product->getWebsiteIds(), $customerGroupId, $appliedRuleId);
        $selectNumber = $this->helperData->getNumberOfCatalogFreeGift($appliedRuleId);

        // check select number free gift product
        if ( (count($selectedFreeGift) > $selectNumber) && $selectNumber !== 0 )
            return false;
            
        // add product 
        if ( empty($productIds) ){
            $item = $this->cartitem->create();
            $item->setProduct($product);
            $item->setQty($qty);
            $quote->addItem($item);
        }
        if ( $productIds ){
            // option product key
            $key = md5(md5($product->getId()).rand(0, 999).time());
            $option = [
                [ 'product_id' => $productId, 'code' => 'freegift_key', 'value' => $key],
                [ 'product_id' => $productId, 'code' => 'has_freegift', 'value' => 1 ]
            ];

            // check all select free gift
            if ( $selectNumber === 0 ){
                $selectedFreeGift = $productIds;
            }
            
            // add product
            $item = $this->cartitem->create();
            $item->setProduct($product);
            $item->setQty($qty);
            $item->setOptions($option);
            $quote->addItem($item);
            
            // add product free gift
            foreach ( $selectedFreeGift as $freeGift ){
                if ( !in_array($freeGift, $productIds) || empty($freeGift) ) 
                    continue;
                else {
                    $productFreeGift = $this->productFactory->create()->load($freeGift);
                    $item = $this->cartitem->create();
                    $item->setProduct($productFreeGift);
                    $item->setCustomPrice(0);
                    $item->setOriginalCustomPrice(0);
                    $item->setQty(1);
                    // option product key
                    $type = 'freegift';
                    switch($productFreeGift->getTypeId()){
                        case 'configurable':
                            $type = 'freegift_configurable';
                            break;
                    }
                    $optionProductFree = [
                        [ 'product_id' => $freeGift, 'code' => 'freegift_catalog', 'value' => 1 ],
                        [ 'product_id' => $freeGift, 'code' => 'freegift_key',     'value' => $key ],
                        [ 'product_id' => $freeGift, 'code' => 'product_type',     'value' => $type ]
                    ];
                    $item->setOptions($optionProductFree);
                    $quote->addItem($item);
                }
            }
        }
        $this->saveRepository->save($quote);
        $quote->collectTotals()->save();
        return true;
    }

// // // // Sales Rules // // // //
    public function getProductFGbyQuoteId($quoteId)
    {
        $quote = $this->quoteFactory->create()->loadByIdWithoutStore($quoteId);
        if ( !$quote->getData() )
            return [];

        $customerGroupId = $quote->getCustomerGroupId();
        $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
        $productIds = $this->helperData->getShoppingCartFreeGiftProductIds($address, 1, $customerGroupId);
        $appliedRuleId = $address->getAppliedFreeGiftRule();
        $selectNumber = $this->helperData->getNumberOfSalesFreeGift($appliedRuleId);

        $count = 0;
        foreach ( $address->getQuote()->getAllItems() as $item ){
            $freeGiftSalesOpt = $item->getOptionByCode('freegift_sales');        
            if( $freeGiftSalesOpt && $freeGiftSalesOpt->getValue() ){
                $count++;
            }
        }
        if ( $count == $selectNumber )
            return [];

        $collection = $this->productCollectionFactory->create()->addAttributeToSelect('*')
                            ->addAttributeToFilter('entity_id',['in' => $productIds]);
        
        if ( $collection->getItems() ){
            $this->catalogRules->setSelect($selectNumber);
            $this->catalogRules->setFreegiftProducts($collection->getItems());
            return $this->catalogRules;
        }
        else 
            return [];
    }

    public function addCartSalesRules($quoteId, $freeProductIds)
    {
        // check empty quote id
        $quote = $this->quoteFactory->create()->loadByIdWithoutStore($quoteId);
        if ( !$quote->getData() ){
            return false;
        }
        $customerGroupId = $quote->getCustomerGroupId();
        $storeId = $quote->getStoreId();
        $selectedFreeGift = explode(',', $freeProductIds);
        
        $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
        $productIds = $this->helperData->getShoppingCartFreeGiftProductIds($address, $storeId, $customerGroupId);
        $appliedRuleId = $address->getAppliedFreeGiftRule();
        $selectNumber = $this->helperData->getNumberOfSalesFreeGift($appliedRuleId);

        $count = 0;
        foreach ( $address->getQuote()->getAllItems() as $item ){
            $freeGiftSalesOpt = $item->getOptionByCode('freegift_sales');
            if( $freeGiftSalesOpt && $freeGiftSalesOpt->getValue() ){
                $count += $item->getQty();
            }
        }
        if ( $count >= $selectNumber || count($selectedFreeGift) > $selectNumber )
            return false;

        // add product free gift
        if ( $productIds ){
            // check product freegift sales in cart, and change qty
            foreach ( $quote->getAllItems() as $item ){
                if ( !$item->getOptionByCode('freegift_sales') )
                    continue;
                if ( in_array($item->getProductId(), $selectedFreeGift) ){
                    $qty = count(array_keys($selectedFreeGift, $item->getProductId()));
                    $qty += $item->getQty();
                    $item->setQty($qty);

                    // unset product id in selectedFreeGift
                    $selectedFreeGift = array_diff($selectedFreeGift, [$item->getProductId()]);
                }
            }
            
            // add product free gift
            foreach ( array_count_values($selectedFreeGift) as $freeGift => $qty ){
                if ( !in_array($freeGift, $productIds) || empty($freeGift) ) 
                    continue;
                else {
                    $productFreeGift = $this->productFactory->create()->load($freeGift);
                    $item = $this->cartitem->create();
                    $item->setProduct($productFreeGift);
                    $item->setCustomPrice(0);
                    $item->setOriginalCustomPrice(0);
                    $item->setQty($qty);
                    // option product key
                    $type = 'freegift_sales_simple';
                    switch($productFreeGift->getTypeId()){
                        case 'configurable':
                            $type = 'freegift_sales_configurable';
                            break;
                    }
                    $optionProductFree = [
                        [ 'product_id' => $freeGift, 'code' => "freegift_sales", 'value' => 1 ],
                        [ 'product_id' => $freeGift, 'code' => 'product_type',   'value' => $type ]
                    ];
                    $item->setOptions($optionProductFree);
                    $quote->addItem($item);
                    $quote->save();
                }
            }
            // $quote->setItemsQty($totalQty);
            // $quote->setItemsCount( count($quote->getAllItems()) );
            // $quote->save();
            $this->saveRepository->save($quote);
            $quote->collectTotals()->save();
            return true;
        }
        return false;
    }

}
