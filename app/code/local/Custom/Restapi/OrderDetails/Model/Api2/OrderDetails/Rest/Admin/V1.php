<?php

class Custom_Restapi_OrderDetails_Model_Api2_OrderDetails_Rest_Admin_V1 extends Mage_Api2_Model_Resource
{

    /**
     * Retrieve a group name by ID
     * @return string
     */

    public function _retrieve()
    {
        //retrieve a group name by ID
//        $customerGroupId = $this->getRequest()->getParam('id');
//        $groupname = Mage::getModel('customer/group')->load($customerGroupId)->getCustomerGroupCode();
//
//        return $groupname;

        $order = Mage::getModel('sales/order')->loadByIncrementId('100000021'); //use a real increment order id here
        $items = $order->getAllVisibleItems();

        return $items[0]->getSku();


    }

}
?>