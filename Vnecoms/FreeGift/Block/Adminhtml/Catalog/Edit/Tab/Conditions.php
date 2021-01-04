<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Block\Adminhtml\Catalog\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;

class Conditions extends \Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit\Tab\Conditions
{
    /**
     * @return Form
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_promo_catalog_rule');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->addTabToForm($model,'conditions_fieldset','freegift_catalog_rule_form');
        
        $fieldset = $form->getElement('conditions_fieldset');

        $this->setForm($form);

        return Generic::_prepareForm();
    }
}
