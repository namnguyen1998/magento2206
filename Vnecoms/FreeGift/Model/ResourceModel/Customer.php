<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Model\ResourceModel;

class Customer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ves_freegift_salesrule_customer', 'rule_customer_id');
    }

    /**
     * Get rule usage record for a customer
     *
     * @param \Vnecoms\FreeGift\Model\Customer $rule
     * @param int $customerId
     * @param int $ruleId
     * @return $this
     */
    public function loadByCustomerRule($rule, $customerId, $ruleId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable()
        )->where(
            'customer_id = :customer_id'
        )->where(
            'rule_id = :rule_id'
        );
        $data = $connection->fetchRow($select, [':rule_id' => $ruleId, ':customer_id' => $customerId]);
        if (false === $data) {
            // set empty data, as an existing rule object might be used
            $data = [];
        }
        $rule->setData($data);
        return $this;
    }
}
