<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Controller\Adminhtml\Promo\Catalog;

class Index extends \Vnecoms\FreeGift\Controller\Adminhtml\Promo\Rule
{
    /**
     * @return void
     */
    public function execute()
    {
/*         $dirtyRules = $this->_objectManager->create('Magento\CatalogRule\Model\Flag')->loadSelf();
        if ($dirtyRules->getState()) {
            $this->messageManager->addNotice($this->getDirtyRulesNoticeMessage());
        } */

        $this->_initAction()->_addBreadcrumb(__('Catalog Rules'), __('Catalog Rules'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Manage Catalog Rules'));
        $this->_view->renderLayout();
    }
}
