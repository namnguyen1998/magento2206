<?php
namespace Vnecoms\FreeGift\Model\ResourceModel\CatalogRule;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * App page collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'rule_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Vnecoms\FreeGift\Model\CatalogRule', 'Vnecoms\FreeGift\Model\ResourceModel\CatalogRule');
    }

}
