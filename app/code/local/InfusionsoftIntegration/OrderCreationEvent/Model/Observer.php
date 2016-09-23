
<?php
/**
 * Our class name should follow the directory structure of
 * our Observer.php model, starting from the namespace,
 * replacing directory separators with underscores.
 * i.e. app/code/local/InfusionsoftIntegration/OrderCreationEvent/Model/Observer.php
 */
class InfusionsoftIntegration_OrderCreationEvent_Model_Observer
{
    /**
     * Magento passes a Varien_Event_Observer object as
     * the first parameter of dispatched events.
     */
    public function logUpdate(Varien_Event_Observer $observer)
    {
        Mage::log("Got event of new order creation");

        // Retrieve the order being created from the event observer
        $order = $observer->getEvent()->getOrder();

        // Write a new line to var/log/product-updates.log

        $incrementId = $order->getIncrementId();
        Mage::log($incrementId);

        $customerName = $order->getCustomerName();
        Mage::log($customerName);

        $customerEmail = $order->getCustomerEmail();
        Mage::log("$customerEmail");

        $subTotal = $order->getTotalDue();
        Mage::log($subTotal);

        $orderDate = $order->getCreatedAtFormated('short');
        Mage::log($orderDate);

        if(Mage::getSingleton('customer/session')->isLoggedIn()){
            // Get group Id
            $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
            //Get customer Group name
            $group = Mage::getModel('customer/group')->load($groupId);
            Mage::log($group->getCode());
        }


        $billingAddress = $order->getBillingAddress();

        $countryCode = $billingAddress->getCountry();
        $billingCountry = Mage::getModel('directory/country')->loadByCode($countryCode)->getName();
        Mage::log($billingCountry);

        Mage::log(print_r($billingAddress, true));

        $shippingAddress = $order->getShippingAddress();
        Mage::log(print_r($shippingAddress, true));

        $items = $order->getAllItems();
        Mage::log("Got items");

        Mage::log(
            "Finished executing the the order created");
    }
}