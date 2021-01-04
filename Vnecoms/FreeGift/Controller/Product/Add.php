<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Controller\Product;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Cart as CustomerCart;

class Add extends \Magento\Catalog\Controller\Product\View
{    
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;
    
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
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    
    /**
     * @var \Vnecoms\FreeGift\Helper\Data
     */
    protected $_helper;
    
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;
    
    /**
     * @var \Vnecoms\FreeGift\Model\SalesRuleFactory
     */
    protected $salesRuleFactory;
    
    /**
     * @var \Vnecoms\FreeGift\Model\SalesRule
     */
    protected $appliedRule;
    
    /**
     * 
     * @param Context $context
     * @param \Magento\Customer\Model\Session $session
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Helper\Product\View $viewHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $session,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Vnecoms\FreeGift\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Vnecoms\FreeGift\Model\SalesRuleFactory $salesRuleFactory,
        CustomerCart $cart
    ) {
        $this->_storeManager = $storeManager;
        $this->_session = $session;
        $this->_cart = $cart;
        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $helper;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->salesRuleFactory = $salesRuleFactory;
        parent::__construct($context, $viewHelper, $resultForwardFactory, $resultPageFactory);
    }
    
    /**
     * Display customer wishlist
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws NotFoundException
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
        $response = new \Magento\Framework\DataObject();
        
        if(!$product->getEntityId()) {
            $response->setData([
               'success' => false,
               'message' => __("The freegift product is not available."), 
            ]);
            return $this->_resultJsonFactory->create()->setJsonData($response->toJson());
        }

        /* Check if the customer add a freegift to shopping cart already*/
        $count = 0;
        foreach($this->_cart->getItems() as $item){
            $freeGiftSalesOpt = $item->getOptionByCode('freegift_sales');
            
            if(
                $freeGiftSalesOpt &&
                $freeGiftSalesOpt->getValue()
            ) {
                $count +=$item->getQty();
            }
        }
        $ruleData = $this->getFreeGiftRuleData();
        $appliedRule = $ruleData['applied_rule'];
        $maxNumberOfFreeGift = $appliedRule->getNoOfFreegift();
        
        if($count >= $maxNumberOfFreeGift){
            $response->setData([
                'success' => false,
                'message' => __("Can not add more than %1 free gift(s).",$maxNumberOfFreeGift),
            ]);
            return $this->_resultJsonFactory->create()->setJsonData($response->toJson());
        }
        
        $availableFreegiftIds = $ruleData['product_ids'];
        if(!in_array($productId, $availableFreegiftIds)){
            $response->setData([
                'success' => false,
                'message' => __("The free gift product is not valid."),
            ]);
            return $this->_resultJsonFactory->create()->setJsonData($response->toJson());
        }
        
        try{
            /* Add the auction product to cart*/
            $product->addCustomOption('freegift_sales', 1);
            $product->addCustomOption('freegift_sales_rule', $appliedRule->getId());           
            // $type = 'freegift_sales';
            $type = 'simple';
            switch($product->getTypeId()){
                case 'configurable':
                    // $type = 'freegift_sales_configurable';
                    $type = 'configurable';
                    break;
            }
                      
            $product->addCustomOption('product_type', $type);
            $params = $this->getRequest()->getParams();
            $params['qty'] = 1;
            $this->_cart->addProduct($product, $params);
           
            $message = __(
               'Free Gift %1 is added to your shopping cart.',
               $product->getName()
            );
            $this->messageManager->addSuccessMessage($message);
            $this->_cart->save();
            
            $response->setData([
                'success' => true,
            ]);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNotice(
                    $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage())
                );
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addError(
                        $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($message)
                    );
                }
            }
            $response->setData([
                'success' => false,
                'message' => $this->viewProductAction(),
                /* 'url' => $this->getUrl('freegift/product/view',['id' => $this->getRequest()->getParam('product_id')]), */
            ]);
        } catch (\Exception $e) {
            $msg = __('We can\'t add this item to your shopping cart right now.');
            $this->messageManager->addException($e, $msg);
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $response->setData([
                'success' => false,
                'message' => $msg,
            ]);
        }
        
        return $this->_resultJsonFactory->create()->setJsonData($response->toJson());
    }
    
    /**
     * 
     * @return multitype:unknown Ambigous <\Vnecoms\FreeGift\Helper\multitype:, multitype:>
     */
    public function getFreeGiftRuleData(){
        $quote = $this->_cart->getQuote();
        $address = $quote->isVirtual()?$quote->getBillingAddress():$quote->getShippingAddress();
        
        $productIds = $this->_helper->getShoppingCartFreeGiftProductIds(
            $address,
            $this->_storeManager->getStore()->getWebsiteId(),
            $this->_session->getCustomerGroupId()
        );
        
        $appliedRule = $this->salesRuleFactory->create();
        $appliedRule->load($address->getAppliedFreeGiftRule());
        
        return [
            'product_ids' => $productIds,
            'applied_rule' => $appliedRule,
        ];
    }
    
    public function viewProductAction(){
        // Get initial data from request
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId = (int) $this->getRequest()->getParam('product_id');
        $specifyOptions = $this->getRequest()->getParam('options');
        
        // Prepare helper and params
        $params = new \Magento\Framework\DataObject();
        $params->setCategoryId($categoryId);
        $params->setSpecifyOptions($specifyOptions);
        
        /* Add catalog_product_view handle*/
        $params->setAfterHandles(['freegift_product_view',/*  'catalog_product_view',  */]);
        
        // Render page
        try {
            $page = $this->resultPageFactory->create();
            $this->viewHelper->prepareAndRender($page, $productId, $this, $params);
            return $page->getLayout()->getBlock('product.info')->toHtml();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $this->noProductRedirect();
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }
    }
}
