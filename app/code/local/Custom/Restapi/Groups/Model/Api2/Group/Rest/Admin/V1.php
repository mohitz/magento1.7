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

        return $order->getAllVisibleItems()[0]->getSku();


//        $orderItems = $order->getItemsCollection()
//            ->addAttributeToSelect('*')
//            ->addAttributeToFilter('product_type', array('eq'=>'simple'))
//            ->load();
//
//        $categoryArr = array();
//        $superCategoryArr = array();
//
//        foreach($orderItems as $sItem) {
//
//            //Ignore conf for now
//            //Alt. Mage_Catalog_Model_Product_Type::TYPE_SIMPLE = 'simple';
//            if($sItem->getProductType() == "simple")
//            {
//
//                $productId = $sItem->getProductId();
//                $sku = $sItem->getSku();
//
//                $parentItemId = $sItem->getParentItemId();
//
//                //Get Parent Item Information
//                $item = Mage::getModel('sales/order_item')->load("$parentItemId"); //use an item_id here
//
//                $productType = $item->getProductType();
//                $parentOrderId = $item->getOrderId();
//                $parentProductId = $item->getProductId();
//                $itemId = $item->getId();
//                $parentItemPrice = $item->getPrice();
//                $quantity = $item->getQtyOrdered();
//
//                //get Active Product Data
//                $nProduct = Mage::getModel('catalog/product')->load($sItem->getProductId());
//                $nSku = $nProduct->getSku();
//                $nUpc = $nProduct->getUpc();
//                $nPrice = $nProduct->getPrice();
//
//                //Fetch category name
//                $product = Mage::getModel('catalog/product')->load($item->getProductId());
//                $categoryIds = $product->getCategoryIds();
//
//
//                foreach ($categoryIds as $category_id) {
//                    $category = Mage::getModel('catalog/category')->setStoreId(Mage::app()->getStore()->getId())->load($category_id);
//
//                    $categoryName = $category->getName();
//                    $categoryArr.array_push($categoryName);
//
//                    $parentCategoryName = $category->getParentCategory()->getName();
//                    $superCategoryArr = array_push($parentCategoryName);
//
//                }
//
//
//            }
//        }
//
//        $categoryString = implode(",", $categoryArr);
//        $superCategoryString = implode(",", $superCategoryArr);
//
//        return $categoryString.$superCategoryString;

    }

}
?>