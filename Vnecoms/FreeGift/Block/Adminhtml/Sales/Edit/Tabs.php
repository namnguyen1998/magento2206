<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vnecoms\FreeGift\Block\Adminhtml\Sales\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('promo_sales_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Free Gift Sales Rule'));
    }
}
