<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\Product;

class BeforeAddProduct
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->quote = $checkoutSession->getQuote();
        $this->productRepository = $productRepository;
    }

    public function beforeAddProduct($subject, $productInfo, $requestInfo = null)
    {      
        if ($productInfo instanceof Product) {
            if (!$productInfo->getId()) {
                throw new LocalizedException(__('We can\'t find the product.'));
            }

            if ($requestInfo && is_array($requestInfo)) {
                if (isset($requestInfo['selected_free_gifts'])) {
                    $selectedFreeGift = trim($requestInfo['selected_free_gifts']);
                    if ($selectedFreeGift) {
                        $productInfo->addCustomOption('has_selected', 1);
                    }
                }
            }
        }
        
        return [$productInfo, $requestInfo];
    }
}
