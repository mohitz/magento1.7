
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
        //$name = print_r($order->getData(), 1);

        Mage::log(
            "Finished executing the the order created");
    }
}