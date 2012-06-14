<?php

/**
 * Create data attribute, can be normal or configurable.
 * 
 * @param string $code
 * @param string $label
 * @param string $datatype varchar, int, decimal, datetime, text
 * @param boolean $configurable If false then normal attribute.
 * @return int Attribute ID
 */
function createDataAttribute($code, $label, $datatype, $configurable) {
	echo "Create ". ($configurable ? 'configurable' : 'normal') ." data attribute $code datatype: $datatype label: $label\n";
	
	$frontendInputs = array(
		'varchar' => 'text',
		'int' => 'text',
		'decimal' => 'text',
		'datetime' => 'date',
		'text' => 'textarea');
	
	$attr = Mage::getModel('catalog/resource_eav_attribute');
	$data = array(
		'attribute_code' => $code,
		'backend_type' => $datatype,
		'frontend_input' => $frontendInputs[ $datatype ],
		'frontend_label' => $label,
		'is_required' => 0,
		'is_user_defined' => 1,
		'default_value' => '',
		'is_unique' => 0,
		'is_global' => 1,
		'is_visible' => 1,
		'is_searchable' => 1,
		'is_filterable' => 1,
		'is_comparable' => 1,
		'is_visible_on_front' => 1,
		'is_html_allowed_on_front' => 1,
		'is_used_for_price_rules' => 1,
		'is_filterable_in_search' => 1,
		'used_in_product_listing' => 1,
		'used_for_sort_by' => 1,
		'is_configurable' => $configurable ? 1 : 0,
		'is_visible_in_advanced_search' => 1,
		'is_used_for_promo_rules' => 1);
	$productEntityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
	$attr->setEntityTypeId($productEntityTypeId);
	
	$attr->addData($data);
	$attr->save();
	
	echo "Created ". ($configurable ? 'configurable' : 'normal') ." $datatype attribute $code #{$attr->getId()}.\n";
	return $attr->getId();
}

/**
 * Create select attribute with options, can be normal or configurable.
 * 
 * @param string $code
 * @param string $label
 * @param boolean $configurable If false then normal attribute.
 * @param array $options Array of string options.
 * @return int Attribute ID
 */
function createSelectAttribute($code, $label, $configurable, $options) {
	echo "Create ". ($configurable ? 'configurable' : 'normal') ." attribute $code label: $label opts: ". join(', ', $options) ."\n";
	
	$attr = Mage::getModel('catalog/resource_eav_attribute');
	$data = array(
		'attribute_code' => $code,
		'backend_type' => 'int',
		'frontend_input' => 'select',
		'frontend_label' => $label,
		'is_required' => 0,
		'is_user_defined' => 1,
		'default_value' => 0,
		'is_unique' => 0,
		'is_global' => 1,
		'is_visible' => 1,
		'is_searchable' => 1,
		'is_filterable' => 1,
		'is_comparable' => 1,
		'is_visible_on_front' => 1,
		'is_html_allowed_on_front' => 1,
		'is_used_for_price_rules' => 1,
		'is_filterable_in_search' => 1,
		'used_in_product_listing' => 1,
		'used_for_sort_by' => 1,
		'is_configurable' => $configurable ? 1 : 0,
		'is_visible_in_advanced_search' => 1,
		'is_used_for_promo_rules' => 1);
	$productEntityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
	$attr->setEntityTypeId($productEntityTypeId);
	
	// Add options
	$data['option'] = array('value' => array(), 'order' => array());
	for ($i = 0; $i < count($options); $i++) {
		$option = $options[$i];
		$placeholder_id = "option_" . ($i+1);
		$data['option']['value'][$placeholder_id] = array(0 => $option);
		$data['option']['order'][$placeholder_id] = $i + 1;
	}
	// set default value
	$data['default'] = array('option_1');
	
	$attr->addData($data);
	$attr->save();
	
	echo "Created ". ($configurable ? 'configurable' : 'normal') ." select attribute #{$attr->getId()}.\n";
	return $attr->getId();
}

