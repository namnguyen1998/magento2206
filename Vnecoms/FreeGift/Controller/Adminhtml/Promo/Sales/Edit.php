<?php
namespace Vnecoms\FreeGift\Controller\Adminhtml\Promo\Sales;

class Edit extends \Vnecoms\FreeGift\Controller\Adminhtml\Promo\Rule
{
    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Vnecoms\FreeGift\Model\SalesRule');

        if ($id) {
            $model->load($id);
            if (!$model->getRuleId()) {
                $this->messageManager->addError(__('This rule no longer exists.'));
                $this->_redirect('freegift/*');
                return;
            }
        }

        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getRuleData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        
        $rule = $this->_objectManager->create('Magento\SalesRule\Model\Rule');
        if($model->getId()){
            $rule->setConditionsSerialized($model->getConditionsSerialized());
        }
        $rule->getConditions()->setFormName('freegift_sales_rule_form');
        $rule->getConditions()->setJsFormObject(
            $rule->getConditionsFieldSetId($rule->getConditions()->getFormName())
        );
        
        $this->_coreRegistry->register('current_promo_sales_rule', $rule);

        $this->_coreRegistry->register('current_rule', $model);
        $this->_coreRegistry->register('rule', $model);

        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Free Gift'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getRuleId() ? $model->getName() : __('New Sales Rule')
        );


        $breadcrumb = $id ? __('Edit Rule') : __('New Rule');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->renderLayout();
    }
}
