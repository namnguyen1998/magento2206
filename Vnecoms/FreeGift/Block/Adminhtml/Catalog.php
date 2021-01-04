<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vnecoms\FreeGift\Block\Adminhtml;

class Catalog extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Vnecoms_FreeGift';
        $this->_controller = 'adminhtml_catalog';
        $this->_headerText = __('Free Gift Catalog Rule');
        $this->_addButtonLabel = __('Add New Rule');
        parent::_construct();
    }
}
