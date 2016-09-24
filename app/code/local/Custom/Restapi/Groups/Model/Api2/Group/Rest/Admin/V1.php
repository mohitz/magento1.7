<?php

class Custom_Restapi_Groups_Model_Api2_Group_Rest_Admin_V1 extends Mage_Api2_Model_Resource {

    /**
     * Retrieve order details by increment id
     * @return string
     */

    public function _retrieve()
    {

        $incrementId = $this->getRequest()->getParam('id');
        $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);

        $orderItems = $order->getItemsCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('product_type', array('eq'=>'simple'))
            ->load();


        $categoryArr = array();
        $superCategoryArr = array();

        foreach($orderItems as $sItem) {

            Mage::log("Entering loop for new item again");

            //Ignore conf for now
            //Alt. Mage_Catalog_Model_Product_Type::TYPE_SIMPLE = 'simple';
            if($sItem->getProductType() == "simple")
            {

                Mage::log("Product is a simple product");

                $productId = $sItem->getProductId();
                $sku = $sItem->getSku();

                $parentItemId = $sItem->getParentItemId();

                //Get Parent Item Information
                $item = Mage::getModel('sales/order_item')->load("$parentItemId"); //use an item_id here

                $productType = $item->getProductType();
                $parentOrderId = $item->getOrderId();
                $parentProductId = $item->getProductId();
                $itemId = $item->getId();
                Mage::log($itemId." -- item id of parent");
                $parentItemPrice = $item->getPrice();
                $quantity = $item->getQtyOrdered();

                //get Active Product Data
                $nProduct = Mage::getModel('catalog/product')->load($sItem->getProductId());
                $nSku = $nProduct->getSku();
                $nUpc = $nProduct->getUpc();
                $nPrice = $nProduct->getPrice();

                //Fetch category name
                $product = Mage::getModel('catalog/product')->load($sItem->getProductId());
                $categoryIds = $product->getCategoryIds();


                foreach ($categoryIds as $category_id) {
                    Mage::log("Entering inner loop for category ids");
                    $category = Mage::getModel('catalog/category')->setStoreId(Mage::app()->getStore()->getId())->load($category_id);

                    $categoryName = $category->getName();
                    Mage::log($categoryName);
                    array_push($categoryArr, $categoryName);

                    $parentCategoryName = $category->getParentCategory()->getName();
                    Mage::log($parentCategoryName);
                    array_push($superCategoryArr, $parentCategoryName);

                }


            }
            else {
                Mage::log("Product is not a simple product");
            }
        }

        $categoryString = implode(",", $categoryArr);
        $superCategoryString = implode(",", $superCategoryArr);

        return $categoryString.$superCategoryString;

    }

}
?>