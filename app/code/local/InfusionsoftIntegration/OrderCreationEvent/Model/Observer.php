
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

        $billingAddressString = $billingAddress->getName().$billingAddress->getStreetFull().$billingAddress->getRegion().$billingCountry;

        Mage::log($billingAddressString);

        $shippingAddress = $order->getShippingAddress();
        $shipcountryCode = $shippingAddress->getCountry();
        $shippingCountry = Mage::getModel('directory/country')->loadByCode($shipcountryCode)->getName();
        $shippingAddressString = $shippingAddress->getName().$shippingAddress->getStreetFull().$shippingAddress->getRegion().$shippingCountry;
        Mage::log(print_r($shippingAddressString, true));

        $items = $order->getAllItems();


        foreach ($items as $itemId => $item)
        {

            Mage::log($itemId);
            Mage::log($item->getName());
            Mage::log($item->getPrice());
            Mage::log($item->getSku());
            Mage::log($item->getProductId());
            Mage::log($item->getQtyOrdered());

            //$size = $item->getResource()->getAttribute('size')->getFrontend()->getValue($item);
            //$color =  $item->getResource()->getAttribute('color')->getFrontend()->getValue($item);

            $size = $item->getData('size');
            $color = $item->getData('color');
            Mage::log($size);
            Mage::log($color);

            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $categoryIds = $product->getCategoryIds();
            if (isset($categoryIds[0])) {
                $category = Mage::getModel('catalog/category')->setStoreId(Mage::app()->getStore()->getId())->load($categoryIds[0]);
                Mage::log($category->getName());
            }

            Mage::log("Done with a item");

        }

        Mage::log("Got items");

        Mage::log(
            "Finished executing the the order created");
    }
}