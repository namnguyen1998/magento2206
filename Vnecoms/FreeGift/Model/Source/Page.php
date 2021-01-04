<?php
namespace Vnecoms\FreeGift\Model\Source;

class Page implements \Magento\Framework\Option\ArrayInterface
{

    const PAGE_SHOPPING_CART    = 'checkout_cart_index';
    const PAGE_PRODUCT          = 'catalog_product_view';
    const PAGE_CATEGORY         = 'catalog_category_view';
    const PAGE_ONESTEPCHECKOUT  = 'onestepcheckout_index_index';
    
    /**
     * Options array
     *
     * @var array
     */
    protected $_options = null;
    
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                [
                    'label' => __('Shopping Cart Page'), 
                    'value' => self::PAGE_SHOPPING_CART
                ],
                [
                    'label' => __('Product Detail Page'),
                    'value' => self::PAGE_PRODUCT
                ],
                [
                    'label' => __('Category Page'),
                    'value' => self::PAGE_CATEGORY
                ],
                [
                    'label' => __('One Step Checkout Page'),
                    'value' => self::PAGE_ONESTEPCHECKOUT
                ],
            ];
        }
        return $this->_options;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = [];
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }
    
    
    /**
     * Get options as array
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

}
