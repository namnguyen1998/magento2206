<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Block\Adminhtml\Sales\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Actions extends Generic implements TabInterface
{
    /**
     * Prepare content for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabLabel()
    {
        return __('Free Products');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Free Products');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return Form
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_rule');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset(
            'action_fieldset',
            ['legend' => __('Free Products')]
        );
        
        $fieldset->addField(
            'no_of_freegift',
            'text',
            [
                'name' => 'no_of_freegift',
                'required' => true,
                'class' => 'validate-greater-than-zero',
                'label' => __('Number of Free Products'),
                'note' => __('e.g. Customer can select 2 from 5 free gifts.'),
            ]
        );
        
        /* $fieldset->addField(
            'stop_rules_processing',
            'select',
            [
                'name' => 'stop_rules_processing',
                'label' => __('Discard subsequent rules'),
                'options' => [
                    '0' => __('No'),
                    '1' => __('Yes'),
                ]
            ]
        ); */

        $form->setFieldNameSuffix('rule');
        $form->setValues($model->getData());

        //$form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }
    
    
    /**
     * (non-PHPdoc)
     * @see \Magento\Backend\Block\Widget\Form::_prepareLayout()
     */
    protected function _prepareLayout(){
        return parent::_prepareLayout();
    }
}
