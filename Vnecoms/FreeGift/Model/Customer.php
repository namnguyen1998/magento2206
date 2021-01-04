<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Model;

class Customer extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Vnecoms\FreeGift\Model\ResourceModel\Customer');
    }

    /**
     * Load by customer rule
     *
     * @param int $customerId
     * @param int $ruleId
     * @return $this
     */
    public function loadByCustomerRule($customerId, $ruleId)
    {
        $this->_getResource()->loadByCustomerRule($this, $customerId, $ruleId);
        return $this;
    }
}
