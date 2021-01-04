<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Controller\Adminhtml\Promo\Sales;

class NewAction extends \Vnecoms\FreeGift\Controller\Adminhtml\Promo\Rule
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
