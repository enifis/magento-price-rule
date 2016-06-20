<?php
/**
 * Overwrites Mage_CatalogRule_Model_Observer. The only changed code
 * is indicatet by comment "THIS LINE WAS CHANGED".
 */

/**
 * Catalog Price rules observer model
 */
class Enifis_PriceRule_Model_Observer extends Mage_CatalogRule_Model_Observer
{
     /**
     * Apply catalog price rules to product on frontend
     *
     * @param   Varien_Event_Observer $observer
     *
     * @return  Mage_CatalogRule_Model_Observer
     */
    public function processFrontFinalPrice($observer)
    {
        $product    = $observer->getEvent()->getProduct();
        $pId        = $product->getId();
        $storeId    = $product->getStoreId();

        if ($observer->hasDate()) {
            $date = $observer->getEvent()->getDate();
        } else {
            $date = Mage::app()->getLocale()->storeTimeStamp($storeId);
        }

        if ($observer->hasWebsiteId()) {
            $wId = $observer->getEvent()->getWebsiteId();
        } else {
            $wId = Mage::app()->getStore($storeId)->getWebsiteId();
        }

        if ($observer->hasCustomerGroupId()) {
            $gId = $observer->getEvent()->getCustomerGroupId();
        } elseif ($product->hasCustomerGroupId()) {
            $gId = $product->getCustomerGroupId();
        } else {
            $gId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }

        $key = $this->_getRulePricesKey(array($date, $wId, $gId, $pId));
        if (!isset($this->_rulePrices[$key])) {
            $rulePrice = Mage::getResourceModel('catalogrule/rule')
                ->getRulePrice($date, $wId, $gId, $pId);
            $this->_rulePrices[$key] = $rulePrice;
        }
        if ($this->_rulePrices[$key]!==false) {
            //$finalPrice = min($product->getData('final_price'), $this->_rulePrices[$key]);
            $finalPrice = $this->_rulePrices[$key];
            $product->setFinalPrice($finalPrice);
        }
        return $this;
    }
    
    
     /**
     * Apply catalog price rules to product in admin
     *
     * @param   Varien_Event_Observer $observer
     *
     * @return  Mage_CatalogRule_Model_Observer
     */
    public function processAdminFinalPrice($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $storeId = $product->getStoreId();
        $date = Mage::app()->getLocale()->storeDate($storeId);
        $key = false;

        if ($ruleData = Mage::registry('rule_data')) {
            $wId = $ruleData->getWebsiteId();
            $gId = $ruleData->getCustomerGroupId();
            $pId = $product->getId();

            $key = $this->_getRulePricesKey(array($date, $wId, $gId, $pId));
        }
        elseif (!is_null($storeId) && !is_null($product->getCustomerGroupId())) {
            $wId = Mage::app()->getStore($storeId)->getWebsiteId();
            $gId = $product->getCustomerGroupId();
            $pId = $product->getId();
            $key = $this->_getRulePricesKey(array($date, $wId, $gId, $pId));
        }

        if ($key) {
            if (!isset($this->_rulePrices[$key])) {
                $rulePrice = Mage::getResourceModel('catalogrule/rule')
                    ->getRulePrice($date, $wId, $gId, $pId);
                $this->_rulePrices[$key] = $rulePrice;
            }
            if ($this->_rulePrices[$key]!==false) {
                //$finalPrice = min($product->getData('final_price'), $this->_rulePrices[$key]);
                // THIS LINE WAS CHANGED
                $finalPrice = $this->_rulePrices[$key];
                $product->setFinalPrice($finalPrice);
            }
        }

        return $this;
    }
}
