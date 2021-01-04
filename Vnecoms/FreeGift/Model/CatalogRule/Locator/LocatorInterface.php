<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Model\CatalogRule\Locator;

/**
 * Interface LocatorInterface
 */
interface LocatorInterface
{
    /**
     * @return \Vnecoms\FreeGift\Model\CatalogRule
     */
    public function getRule();

    /**
     * @return array
     */
    public function getWebsiteIds();

}
