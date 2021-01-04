<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Cart as CustomerCart;

class UpdateFreeProductPrice implements ObserverInterface
{
    /**
     * Add free gift to shopping cart.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Product */
        $product = $observer->getProduct();        
       
        if(
            $product->getCustomOption('freegift_catalog') ||
            $product->getCustomOption('freegift_sales')
        ) {
            $product->setFinalPrice(0);
        }
    }
}
