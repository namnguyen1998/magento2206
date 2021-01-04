<?php
namespace Vnecoms\FreeGift\Controller\Adminhtml\Promo\Catalog;

class Delete extends \Vnecoms\FreeGift\Controller\Adminhtml\Promo\Rule
{
    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Vnecoms\FreeGift\Model\CatalogRule');

        if ($id) {
            $model->load($id);
            if (!$model->getRuleId()) {
                $this->messageManager->addError(__('This rule no longer exists.'));
            }
            
            try{
                $model->delete();
                $this->messageManager->addSuccess(__("Your rule has been deleted."));
            }catch (\Exception $e){
                $this->messageManager->addError($e->getMessage());
            }
        }else{
            $this->messageManager->addError(__("The rule id is invalid."));
        }
        
        $this->_redirect('freegift/*');
    }
}
