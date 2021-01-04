<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vnecoms\FreeGift\Model\Api\Data;

/**
 * Vnecoms FreeGift date model
 */
class CatalogRules extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Vnecoms\FreeGift\Api\Data\CatalogRulesInterface
{

    /**
     * Get select
     *
     * @return int
     */
    public function getSelect()
    {
        return $this->_get(self::SELECT);
    }

    /**
     * Set select
     *
     * @param int $select
     * @return $this
     */
    public function setSelect($select)
    {
        return $this->setData(self::SELECT, $select);
    }


     /**
     * Get Freegift Products 
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getFreegiftProducts()
    {
        return $this->_get(self::FREEGIFT_PRODUCTS);
    }

    /**
     * Set Freegift Products 
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface[]
     * @return $this
     */
    public function setFreegiftProducts(array $products)
    {
        return $this->setData(self::FREEGIFT_PRODUCTS, $products);
    }
}
