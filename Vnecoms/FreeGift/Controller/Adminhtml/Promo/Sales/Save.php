<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Controller\Adminhtml\Promo\Sales;

use Magento\Framework\Exception\LocalizedException;

class Save extends \Vnecoms\FreeGift\Controller\Adminhtml\Promo\Rule
{
    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            /** @var \Vnecoms\FreeGift\Model\CatalogRule $model */
            $model = $this->_objectManager->create('Vnecoms\FreeGift\Model\SalesRule');
            try {
                $this->_eventManager->dispatch(
                    'adminhtml_controller_freegift_salesrule_prepare_save',
                    ['request' => $this->getRequest()]
                );
                $data = $this->getRequest()->getPostValue();
                $data = $data['rule'];
                $id = $this->getRequest()->getParam('rule_id');
                if ($id) {
                    $model->load($id);
                }

                $validateResult = $model->validateData(new \Magento\Framework\DataObject($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addError($errorMessage);
                    }
                    $this->_getSession()->setPageData($data);
                    $this->_redirect('freegift/*/edit', ['id' => $model->getId()]);
                    return;
                }

                if (isset($data['conditions'])) {
                    /* Process rule conditions*/
                    $tmpData = ['conditions'=>$data['conditions']];
                    
                    /** @var $tmpRule \Magento\SalesRule\Model\Rule */
                    $tmpRule = $this->_objectManager->create('Vnecoms\FreeGift\Model\TmpSalesRule');

                    $tmpRule->loadPost($tmpData);
                    $data['conditions_serialized'] = $tmpRule->getSerializedConditions();
                }
                if(isset($data['freegift']) && is_array($data['freegift'])){
                    $freeProductIds = [];
                    foreach($data['freegift'] as $product){
                        $freeProductIds[] = $product['id'];
                    }
                    
                    $data['product_ids'] = $freeProductIds;
                }elseif(isset($data['freegift_product_listing']) && is_array($data['freegift_product_listing'])){
                    $freeProductIds = [];
                    foreach($data['freegift_product_listing'] as $product){
                        $freeProductIds[] = $product['entity_id'];
                    }
                    
                    $data['product_ids'] = $freeProductIds;
                }else{
                    $data['product_ids'] = explode("&", trim($data['product_ids'],'&'));
                    $data['product_ids'] = implode(",", $data['product_ids']);
                }
                
                unset($data['freegift']);
                unset($data['freegift_product_listing']);
                
                $model->loadPost($data);

                $this->_objectManager->get('Magento\Backend\Model\Session')->setRuleData($data);

                $model->save();

                $this->messageManager->addSuccess(__('You saved the rule.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setRuleData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('freegift/*/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('freegift/*/');
                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the rule data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('freegift/*/edit', ['id' => $this->getRequest()->getParam('rule_id')]);
                return;
            }
        }
        $this->_redirect('freegift/*/');
    }
}
