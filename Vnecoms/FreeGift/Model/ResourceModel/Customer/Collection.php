<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Model\ResourceModel\Customer;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'rule_customer_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Vnecoms\FreeGift\Model\Customer', 
            'Vnecoms\FreeGift\Model\ResourceModel\Customer'
        );
    }
}
