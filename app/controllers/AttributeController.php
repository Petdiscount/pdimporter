<?php
/**
 * Petdiscount Magento Importer
 *
 * @author Ruthger Idema <ruthger.idema@gmail.com>
 * @license http://creativecommons.org/licenses/MIT/ MIT
 */

class AttributeController {

    /**
     * @param $code
     * @param $label
     * @param $attribute_type
     * @param $product_type
     * @param $group_name
     * @return string
     */
    public function createAttribute($code, $label, $attribute_type, $product_type, $group_name)
    {

        /**
         * Check if attribute exists
         * Otherwise create
         */

        $entity = 'catalog_product';
        $attribute = $code;
        $check = Mage::getResourceModel('catalog/eav_attribute')
            ->loadByCode($entity,$attribute);

        if ($check->getId()) {

            /**
             * Attribute exists
             */

            return "Attribute already exists";
        }


        $_attribute_data = array(
            'attribute_code' => $code,
            'is_global' => '1',
            'frontend_input' => $attribute_type, //'boolean',
            'default_value_text' => '',
            'default_value_yesno' => '0',
            'default_value_date' => '',
            'default_value_textarea' => '',
            'is_unique' => '0',
            'is_required' => '0',
            'apply_to' => array($product_type), //array('grouped')
            'is_configurable' => '0',
            'is_searchable' => '0',
            'is_visible_in_advanced_search' => '0',
            'is_comparable' => '0',
            'is_used_for_price_rules' => '0',
            'is_wysiwyg_enabled' => '0',
            'is_html_allowed_on_front' => '1',
            'is_visible_on_front' => '0',
            'used_in_product_listing' => '0',
            'used_for_sort_by' => '0',
            'frontend_label' => array($label)
        );


        $model = Mage::getModel('catalog/resource_eav_attribute');

        if (!isset($_attribute_data['is_configurable'])) {
            $_attribute_data['is_configurable'] = 0;
        }
        if (!isset($_attribute_data['is_filterable'])) {
            $_attribute_data['is_filterable'] = 0;
        }
        if (!isset($_attribute_data['is_filterable_in_search'])) {
            $_attribute_data['is_filterable_in_search'] = 0;
        }

        if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
            $_attribute_data['backend_type'] = $model->getBackendTypeByInput($_attribute_data['frontend_input']);
        }

        $defaultValueField = $model->getDefaultValueByInput($_attribute_data['frontend_input']);
        if ($defaultValueField) {
            $_attribute_data['default_value'] = "";//$this->getRequest()->getParam($defaultValueField);
        }


        $model->addData($_attribute_data);

        $model->setEntityTypeId(Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId());
        $model->setIsUserDefined(1);


        try {
            $model->save();

            /**
             * Assign attribute to attribute group
             */
            $attribute_set_name = 'Default';
            $attribute_code = $code;

            $setup = new Mage_Eav_Model_Entity_Setup('core_setup');

            //-------------- add attribute to set and group
            $attribute_set_id=$setup->getAttributeSetId('catalog_product', $attribute_set_name);
            $attribute_group_id=$setup->getAttributeGroupId('catalog_product', $attribute_set_id, $group_name);
            $attribute_id=$setup->getAttributeId('catalog_product', $attribute_code);

            $setup->addAttributeToSet($entityTypeId='catalog_product',$attribute_set_id, $attribute_group_id, $attribute_id);


        } catch (Exception $e) { echo 'Sorry, error occured while trying to save the attribute. Error: '.$e->getMessage().'</p>'; }
    }

    /**
     * @param $group_name
     * @return string
     */
    public function CreateAttributeGroup($group_name){

        /**
         * Create the Attribute group
         *
         * Hint:
         * Attribute set > Attribute Group > Attribute
         */

        $model = Mage::getModel('eav/entity_setup', 'core_setup');
        $attributeSetId = $model->getAttributeSetId('catalog_product', 'Default');
        $attributeGroupId = $model->getAttributeGroup('catalog_product', $attributeSetId, $group_name);

        /**
         * Check if Attribute Group exists
         * Otherwise create
         */
        if($attributeGroupId){
            return "already exists";
        }


        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $entityTypeId = $setup->getEntityTypeId('catalog_product');
        $attributeSetId = $setup->getDefaultAttributeSetId($entityTypeId);
        $setup->addAttributeGroup($entityTypeId, $attributeSetId, $group_name, 100);
        $attributeGroupId = $setup->getAttributeGroupId($entityTypeId, $attributeSetId, $group_name);
        return 'Attribute group "' . $group_name . '" with id: "'. $attributeGroupId . ' created';
    }


    /**
     * @param $attcode
     * @param $value
     * @return bool
     */
    public function AddValueToAttribute($attcode, $value){

        /**
         * If exists, return ID
         */
        if($this->attributeValueExists($attcode, $value)){
            return $this->attributeValueExists($attcode, $value);
        }

        /**
         * Create new attribute
         * Return ID
         */
        if($this->attributeValueExists($attcode, $value)){
            return $this->attributeValueExists($attcode, $value);
        }

        $attr_model = Mage::getModel('catalog/resource_eav_attribute');
        $attr = $attr_model->loadByCode('catalog_product', $attcode);
        $attr_id = $attr->getAttributeId();

        $option['attribute_id'] = $attr_id;
        $option['value']['any_option_name'][0] = $value;

        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $setup->addAttributeOption($option);

        return $this->attributeValueExists($attcode, $value);


    }

    /**
     * @param $attCode
     * @param $attributeValue
     * @return bool
     */
    public function attributeValueExists($attCode, $attributeValue)
    {

        /**
         * Check if Attribute Value Exists
         *
         */
        $attributeModel = Mage::getModel('eav/entity_attribute');
        $attributeOptionModel = Mage::getModel('eav/entity_attribute_source_table') ;

        //retrieve attribute_id using attribute_code
        $attributeId = $attributeModel->getIdByCode('catalog_product', $attCode);    // [A]
        //load attribute model using attribute_id
        $attribute = $attributeModel->load($attributeId);    // [B]

        $options = $attributeOptionModel->setAttribute($attribute)->getAllOptions(false);

        foreach($options as $option) {
            if ($option['label'] == $attributeValue) {
                return $option['value'];
            }
        }

        return false;
    }


}