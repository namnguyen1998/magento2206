<?php
namespace Vnecoms\FreeGift\Model;

class CatalogRule extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_ENABLED		= 1;
    const STATUS_DISABLED		= 0;
    
    const ACTION_TOGETHER       = 'together';
    const ACTION_SELECT         = 'select';
    
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'freegift_catalog_rule';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getRule() in this case
     *
     * @var string
     */
    protected $_eventObject = 'rule';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Vnecoms\FreeGift\Model\ResourceModel\CatalogRule');
    }
    
    /**
     * Before save
     * 
     * @see \Magento\Framework\Model\AbstractModel::beforeSave()
     */
    public function beforeSave()
    {
        if(is_array($this->getWebsiteIds())){
            $this->setWebsiteIds(implode(',', $this->getWebsiteIds()));
        }
        
        if(is_array($this->getGroupIds())){
            $this->setGroupIds(implode(',', $this->getGroupIds()));
        }
        
        if(is_array($this->getProductIds())){
            $this->setProductIds(implode(',', $this->getProductIds()));
        }
        return parent::beforeSave();
    }
    
    /**
     * Validate Data
     * 
     * @param array $data
     * @return Ambigous <boolean, multitype:>
     */
    public function validateData($data = [])
    {
        $errors = [];
        if(!isset($data['website_ids']) && !is_array($data['website_ids'])){
            $erros[] = __("The website is not set");
        }
        if(!isset($data['group_ids']) && !is_array($data['group_ids'])){
            $erros[] = __("The group is not set");
        }
        if(
            isset($data['from_date']) &&
            isset($data['to_date']) &&
            strtotime($data['from_date']) > strtotime($data['to_date'])
        ) {
            $erros[] = __("From date must to be less than to date");
        }      
        return sizeof($errors) ? $errors : true;
    }
    
    /**
     * Load Poast
     * 
     * @param array $data
     * @return \Vnecoms\FreeGift\Model\CatalogRule
     */
    public function loadPost($data = [])
    {
        $idsArr = ['id', 'rule_id'];
        foreach($data as $key => $value){
            /*Do not set the id if the value is empty*/
            if(in_array($key, $idsArr) && !$value) continue;
            $this->setData($key, $value);
        }
        return $this;
    }
   
}
