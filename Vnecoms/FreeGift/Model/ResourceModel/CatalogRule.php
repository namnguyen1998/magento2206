<?php
// @codingStandardsIgnoreFile
namespace Vnecoms\FreeGift\Model\ResourceModel;

class CatalogRule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ves_freegift_catalogrule', 'rule_id');
    }
}
