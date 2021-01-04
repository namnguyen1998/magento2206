<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Model;

class TmpSalesRule extends \Magento\SalesRule\Model\Rule
{
    /**
     * Get serialized
     * @return string
     */
    public function getSerializedConditions(){
        if(property_exists($this, 'serializer')){
            return $this->serializer->serialize($this->getConditions()->asArray());
        }
        return serialize($this->getConditions()->asArray());
    }
}