<?php
/**
 * Petdiscount Magento Importer
 *
 * @author Ruthger Idema <ruthger.idema@gmail.com>
 * @license http://creativecommons.org/licenses/MIT/ MIT
 */



class ProductController {

    /**
     * @return string
     */
    public function MarkNotImported(){
        $products = Mage::getModel('catalog/product')->getCollection();
        $products->addFieldToFilter('pd_import', TRUE);

        foreach ($products as $product) {
            $product->setPdImport(FALSE);
            $product->getResource()->saveAttribute($product, 'pd_import');
        }

        return "done";


    }

    /**
     * @return string
     */
    public function DisableNotImported(){

        /**
         * You don't want products in your store which are not available
         * So if it isn't in the feed, disable it.
         */

        $products = Mage::getModel('catalog/product')->getCollection();
        $products->addFieldToFilter('pd_product', TRUE);
        $products->addFieldToFilter('pd_import', FALSE);
        $products->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', 1);

        foreach ($products as $product) {

            /**
             * For some unknown reason, this script decided to put all products on Visible Both
             * So a qnd hack so it doesn't happen again
             */

            if($product->getVisibility() == 1){
                $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
            } else {
                $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
            }


            $product->setStatus(FALSE);

            $product->save();
        }

        return "done";

    }

    /**
     * @param $product
     * @return string
     */
    public function UpdateSimpleProduct($product){
        /**
         * Re-enable if product was disabled
         * Mark as imported
         */
        $productupdate = Mage::getModel('catalog/product')->loadByAttribute('sku',$product['sku']);
        $productupdate
            ->setStatus(TRUE)
            ->setPdImport(TRUE)
            ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
            ->setName($product['name'])
        ;
        $productupdate->save();

        /**
         * Update stock
         */
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productupdate->getId());
        if ($stockItem->getId() > 0) {
            $stockItem->setManageStock(1)
                ->setUseConfigManageStock(1)
                ->setQty($product['variants'][0]['stock'])
                ->setIsInStock(1)
                ->save();
        }


        return 'SP ' . $product['sku']  . " updated";


    }


    /**
     * @param $product
     * @return string
     */
    public function UpdateGroupedProduct($product){


        /**
         * Re-enable if product was disabled
         * Mark as imported
         */
        $productupdate = Mage::getModel('catalog/product')->loadByAttribute('sku',$product['sku']);
        $productupdate
            ->setStatus(TRUE)
            ->setPdImport(TRUE)
            ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
            ->setName($product['name'])
        ;
        $productupdate->save();

        foreach($product['variants'] as $variant) {

            $productupdate = Mage::getModel('catalog/product')->loadByAttribute('sku',$variant['sku']);
            if(!$productupdate){
                continue;
            }
            $productupdate
                ->setStatus(TRUE)
                ->setPdImport(TRUE)
                ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)
                ->setName($product['name'] . " - " . $variant['type'])
            ;
            $productupdate->save();

            /**
             * Update stock
             */
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productupdate->getId());
            if ($stockItem->getId() > 0) {
                $stockItem->setManageStock(1)
                    ->setUseConfigManageStock(1)
                    ->setQty($variant['stock'])
                    ->setIsInStock(1)
                    ->save();
            }

        }


        return 'GP ' . $product['sku']  . " updated";
    }


    /**
     * @param $product
     * @return string
     */
    public function CreateSimpleProduct ($product){

        /**
         * Create simple product
         */



        $attributecontroller = new AttributeController();
        $brandid = $attributecontroller->AddValueToAttribute("pd_brand", $product['brand']);

        $categorycontroller = new CategoryController();
        if($product['category']['main']){
            $maincategory = $categorycontroller->CreateCategory($product['category']['main'], 2);
            $subcategory = $categorycontroller->CreateCategory($product['category']['sub'], $maincategory);
        } else {
            $maincategory = 2;
            $subcategory = null;
        }


        $newproduct = Mage::getModel('catalog/product');
        try{
            $newproduct
                ->setWebsiteIds(array(1)) //website ID the product is assigned to, as an array
                ->setAttributeSetId(4) //ID of a attribute set named 'default'
                ->setTypeId('simple') //product type
                ->setCreatedAt(strtotime('now')) //product creation time
                ->setSku($product['sku']) //SKU
                ->setName($product['name']) //product name
                ->setWeight(1.0000)
                ->setStatus(1) //product status (1 - enabled, 2 - disabled)
                ->setTaxClassId(2) //tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
                ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH) //catalog and search visibility
                ->setPrice($product['variants'][0]['b2c']) //price in form 11.22
                ->setMetaTitle($product['name'])
                ->setMetaKeyword('')
                ->setMetaDescription($product['name'])
                ->setDescription($product['description'] . " ")
                ->setShortDescription($product['description'] . " ")
                ->setPdProduct(TRUE)
                ->setPdImport(TRUE)
                ->setPdBrand($brandid)
                ->setPdEanNumber($product['variants'][0]['ean'])
                ->setMediaGallery (array('images'=>array (), 'values'=>array ())) //media gallery initialization
                ->addImageToMediaGallery('temp/images/' . $product['sku'] . '.jpg', array('image','thumbnail','small_image'), false, false) //assigning image, thumb and small image to media gallery
                ->setStockData(array(
                        'use_config_manage_stock' => 1, //'Use config settings' checkbox
                        'manage_stock'=>1, //manage stock
                        'min_sale_qty'=>1, //Minimum Qty Allowed in Shopping Cart
                        'max_sale_qty'=>100, //Maximum Qty Allowed in Shopping Cart
                        'is_in_stock' => 1, //Stock Availability
                        'qty' => $product['variants'][0]['stock'] //qty
                    )
                )

                ->setCategoryIds(array($maincategory, $subcategory)); //assign product to categories
            $newproduct->save();

            return $product['sku'] . ' saved';

        }catch(Exception $e){
            return $e->getMessage();
        }

    }

    /**
     * @param $product
     * @return string
     */
    public function CreateGroupedProduct ($product){

        /**
         * Create category if not exists
         */
        $categorycontroller = new CategoryController();
        if($product['category']['main']){
            $maincategory = $categorycontroller->CreateCategory($product['category']['main'], 2);
            $subcategory = $categorycontroller->CreateCategory($product['category']['sub'], $maincategory);
        } else {
            $maincategory = 2;
            $subcategory = null;
        }



        $attributecontroller = new AttributeController();
        $brandid = $attributecontroller->AddValueToAttribute("pd_brand", $product['brand']);


        /**
         * Create grouped product with children
         */
        $newproduct = Mage::getModel('catalog/product');
        try{
            $newproduct
                ->setWebsiteIds(array(1)) //website ID the product is assigned to, as an array
                ->setAttributeSetId(4) //ID of a attribute set named 'default'
                ->setTypeId('grouped') //product type
                ->setCreatedAt(strtotime('now')) //product creation time
                ->setSku($product['sku']) //SKU
                ->setName($product['name']) //product name
                ->setStatus(1) //product status (1 - enabled, 2 - disabled)
                ->setTaxClassId(2) //tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
                ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH) //catalog and search visibility
                ->setMetaTitle($product['name'])
                ->setMetaKeyword('test meta keyword 2')
                ->setMetaDescription($product['name'])
                ->setDescription($product['description'] . " ")
                ->setShortDescription($product['description'] . " ")
                ->setPdProduct(TRUE)
                ->setPdImport(TRUE)
                ->setPdBrand($brandid)
                ->setMediaGallery (array('images'=>array (), 'values'=>array ())) //media gallery initialization
                ->addImageToMediaGallery('temp/images/' . $product['sku'] . '.jpg', array('image','thumbnail','small_image'), false, false) //assigning image, thumb and small image to media gallery
                ->setStockData(array(
                    'use_config_manage_stock' => 1, //'Use config settings' checkbox
                    'manage_stock'=>1, //manage stock
                    'is_in_stock' => 1, //Stock Availability
                    'stock_status' => 1, //Stock Availability
                    'qty' => '99' //qty
                ))
                ->setCategoryIds(array($maincategory, $subcategory)); //assign product to categories
            $newproduct->save();

            $links = array();

            /**
             * Sort array, so that:
             * Lowest price displays first
             * Sizes, in most cases, are in correct order
             */
            usort($product['variants'], function($a, $b) {
                if($a['b2c']==$b['b2c']) return 0;
                return $a['b2c'] > $b['b2c']?1:-1;
            });


            /**
             * Create the children of the grouped product
             */
            $position = 0;
            foreach($product['variants'] as $variant){

                $newvariant = Mage::getModel('catalog/product');
                $newvariant
                    ->setWebsiteIds(array(1)) //website ID the product is assigned to, as an array
                    ->setAttributeSetId(4) //ID of a attribute set named 'default'
                    ->setTypeId('simple') //product type
                    ->setCreatedAt(strtotime('now')) //product creation time
                    ->setSku($variant['sku']) //SKU
                    ->setName($product['name'] . " - " . $variant['type']) //product name
                    ->setWeight(1.0000)
                    ->setStatus(1) //product status (1 - enabled, 2 - disabled)
                    ->setTaxClassId(2) //tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
                    ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE) //catalog and search visibility
                    ->setPrice($variant['b2c']) //price in form 11.22
                    ->setMetaTitle($product['name'])
                    ->setMetaKeyword('')
                    ->setMetaDescription($product['name'])
                    ->setDescription(" ")
                    ->setShortDescription(" ")
                    ->setPdEanNumber($variant['ean'])
                    ->setPdProduct(TRUE)
                    ->setPdImport(TRUE)
                    ->setMediaGallery (array('images'=>array (), 'values'=>array ())) //media gallery initialization
                    ->addImageToMediaGallery('temp/images/' . $product['sku'] . '.jpg', array('image','thumbnail','small_image'), false, false) //assigning image, thumb and small image to media gallery
                    ->setStockData(array(
                            'use_config_manage_stock' => 1, //'Use config settings' checkbox
                            'manage_stock'=>1, //manage stock
                            'min_sale_qty'=>1, //Minimum Qty Allowed in Shopping Cart
                            'max_sale_qty'=>100, //Maximum Qty Allowed in Shopping Cart
                            'is_in_stock' => 1, //Stock Availability
                            'qty' => $variant['stock'] //qty
                        )
                    ); //assign product to categories
                $newvariant->save();

                /**
                 * Order by price, asc.
                 */
                $links[$newvariant->getId()] = array('qty' => 0, 'position' => $position);
                $position++;

            }


            /**
             * Link the children to their parent
             */

            $linkproducts = Mage::getModel('catalog/product')->load($newproduct->getId());
            unset($newproduct);
            $linkproducts->setGroupedLinkData($links);
            $linkproducts->save();


            /**
             * Product saved!
             */
            return $product['sku'] . ' saved';

        }catch(Exception $e){
            return $e->getMessage();
        }
    }


    public function RemoveProducts(){
        /**
         * Remove all products for testing purposes
         * DONT USE IN PRODUCTION
         */

        $products = Mage::getModel('catalog/product')->getCollection();
        foreach ($products as $product) {
            try {
                $product->delete();
            } catch(Exception $e) {
                echo "Product #".$product->getId()." could not be remvoved: ".$e->getMessage();
            }
        }

        $categoryCollection = Mage::getModel('catalog/category')->getCollection()->addFieldToFilter('level',  array('gteq' => 2));
        foreach($categoryCollection as $category) {
                $category->delete();
            }


    }
}