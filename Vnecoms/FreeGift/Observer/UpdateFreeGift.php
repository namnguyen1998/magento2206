<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\FreeGift\Observer;

use Magento\Framework\Event\ObserverInterface;

class UpdateFreeGift implements ObserverInterface
{
    /**
     * Update free gift quanty
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $observer->getQuote();

        $items = [];
        /*Group items by free gift key*/
        foreach($quote->getAllItems() as $item){
            $freeGiftKey = $item->getOptionByCode('freegift_key');
            if($freeGiftKey && ($fKey = $freeGiftKey->getValue())){
                if(!isset($items[$fKey])){
                    $items[$fKey] = [];
                }
                
                $hasFreeGiftOption = $item->getOptionByCode('has_freegift');
                if($hasFreeGiftOption && $hasFreeGiftOption->getValue()){
                    /*Main Product*/
                    $items[$fKey]['main'] = $item;
                    
                }else{
                    if(!isset($items[$fKey]['free'])) 
                        $items[$fKey]['free'] =[];
                    
                    /*FreeGift product*/
                    $items[$fKey]['free'][] = $item;
                }
            }
        }
        
        if(sizeof($items)){
            foreach($items as $fKey=>$item){                
                if(isset($item['free'])) foreach($item['free'] as $freegift){
                    // $freegift->setQty($item['main']->getQty()); /*Free gift qty is alway equal to main product qty*/
                    $freegift->setQty(1); /*Free gift qty is alway equal 1*/
                }
            }
        }
        return $this;        
    }
}
