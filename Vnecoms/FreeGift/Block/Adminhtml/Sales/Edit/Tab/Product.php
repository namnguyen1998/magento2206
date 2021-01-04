<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vnecoms\FreeGift\Block\Adminhtml\Sales\Edit\Tab;

use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;

class Product extends \Vnecoms\FreeGift\Block\Adminhtml\Catalog\Edit\Tab\Product
{
    public function getAllowedProductTypes(){
        return [
            ProductType::TYPE_SIMPLE,
            ProductType::TYPE_VIRTUAL,
            /* ConfigurableType::TYPE_CODE, */
        ];
    }
    
    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getData(
            'grid_url'
        ) ? $this->getData(
            'grid_url'
        ) : $this->getUrl(
            '*/promo_sales/productGrid',
            ['_current' => true]
        );
    }

}
