<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vnecoms\FreeGift\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Customer group interface.
 * @api
 */
interface CatalogRulesInterface 
{
    /**#@+
     * Constants for keys of data array
     */
    const SELECT                = 'select';
    const FREEGIFT_PRODUCTS     = 'freegift_products';
    
    /**#@-*/

    /**
     * Get select
     *
     * @return  string|null
     */
    public function getSelect();

    /**
     * Set select
     *
     * @param string|null $select
     * @return $this
     */
    public function setSelect($select);


   /**
     * Get Freegift Products 
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getFreegiftProducts();

    /**
     * Set Freegift Products  
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface[] $products
     * @return $this
     */
    public function setFreegiftProducts(array $products);

}
