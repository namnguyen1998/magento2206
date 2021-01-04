<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Controller\Adminhtml\Promo\Sales;

class Index extends \Vnecoms\FreeGift\Controller\Adminhtml\Promo\Rule
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_addBreadcrumb(__('Sales Rules'), __('Sales Rules'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Manage Sales Rules'));
        $this->_view->renderLayout();
    }
}
