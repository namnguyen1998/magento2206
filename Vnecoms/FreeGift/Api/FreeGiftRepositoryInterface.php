<?php
/**
 * 
 * Wiki-Solution
 *
 */

namespace Vnecoms\FreeGift\Api;


/**
 * Class Free Gift Interface
 * @package Vnecoms\FreeGift\Api
 */
interface FreeGiftRepositoryInterface
{
// // // // Catalog Rules // // // //

    /**
     * @param int $productId
     *
     * @return \Vnecoms\FreeGift\Api\Data\CatalogRulesInterface
     */
    public function getProductFGbyProductId($productId);

    /**
     * @param string $quoteId
     * @param int $customerId
     * @param int $productId
     * @param string $freeProductIds
     * @param int $qty
     * @return bool
     */
    public function addCartCatalogRules($quoteId, $customerId, $productId, $freeProductIds, $qty);

// // // // Sales Rules // // // //
    /**
     * @param int $quoteId
     * 
     * @return \Vnecoms\FreeGift\Api\Data\CatalogRulesInterface
     */
    public function getProductFGbyQuoteId($quoteId);

    /**
     * @param string $quoteId
     * @param string $freeProductIds
     * @return bool
     */
    public function addCartSalesRules($quoteId, $freeProductIds);

}