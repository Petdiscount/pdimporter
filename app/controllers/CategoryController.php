<?php
/**
 * Petdiscount Magento Importer
 *
 * @author Ruthger Idema <ruthger.idema@gmail.com>
 * @license http://creativecommons.org/licenses/MIT/ MIT
 */

class CategoryController{

    /**
     * @param $name
     * @param int $parentId
     * @return int
     */
    public function CreateCategory($name, $parentId = 2){

        /**
         * If Category exists, return ID
         */

        $parentCategory = Mage::getModel('catalog/category')->load($parentId);
        $childCategory = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToFilter('is_active', true)
            ->addIdFilter($parentCategory->getChildren())
            ->addAttributeToFilter('name', $name)
            ->getFirstItem()
        ;

        if (null !== $childCategory->getId()) {
           return $childCategory->getId();
        } else {
            try{
                $category = Mage::getModel('catalog/category');
                $category->setName($name);
                $category->setIsActive(1);
                $category->setDisplayMode('PRODUCTS');
                $category->setIsAnchor(1);
                $category->setStoreId(Mage::app()->getStore()->getId());
                $parentCategory = Mage::getModel('catalog/category')->load($parentId);
                $category->setPath($parentCategory->getPath());
                $category->save();

                return $category->getId();
            } catch(Exception $e) {
                mage::log($e);
            }
        }



    }


}