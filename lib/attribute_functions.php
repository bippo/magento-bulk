<?php

/**
 * Create data attribute, can be normal or configurable.
 * 
 * @param string $code
 * @param string $label
 * @param string $backendType varchar, int, decimal, datetime, text
 * @param string $frontendInput text, date, textarea, price
 * @param boolean $configurable If false then normal attribute.
 * @return int Attribute ID
 */
function createDataAttribute($code, $label, $backendType, $frontendInput, $configurable, $overrides = array()) {
	echo "Create ". ($configurable ? 'configurable' : 'normal') ." data attribute $code type: $backendType/$frontendInput label: $label\n";
	
	$attr = Mage::getModel('catalog/resource_eav_attribute');
	$data = array(
		'attribute_code'				=> $code,
		'backend_type'					=> $backendType,
		'frontend_input'				=> $frontendInput,
		'frontend_label'				=> $label,
		'is_required'					=> isset($overrides['is_required']) ? $overrides['is_required'] : 0,
		'is_user_defined'				=> 1,
		'default_value'					=> isset($overrides['default_value']) ? $overrides['default_value'] : '',
		'is_unique'						=> isset($overrides['is_unique']) ? $overrides['is_unique'] : 0,
		'is_global'						=> isset($overrides['is_global']) ? $overrides['is_global'] : 1,
		'is_visible'					=> isset($overrides['is_visible']) ? $overrides['is_visible'] : 1,
		'is_searchable'					=> isset($overrides['is_searchable']) ? $overrides['is_searchable'] : 1,
		'is_filterable'					=> isset($overrides['is_filterable']) ? $overrides['is_filterable'] : 1,
		'is_comparable'					=> isset($overrides['is_comparable']) ? $overrides['is_comparable'] : 1,
		'is_visible_on_front'			=> isset($overrides['is_visible_on_front']) ? $overrides['is_visible_on_front'] : 1,
		'is_html_allowed_on_front'		=> isset($overrides['is_html_allowed_on_front']) ? $overrides['is_html_allowed_on_front'] : 1,
		'is_used_for_price_rules'		=> isset($overrides['is_used_for_price_rules']) ? $overrides['is_used_for_price_rules'] : 1,
		'is_filterable_in_search'		=> isset($overrides['is_filterable_in_search']) ? $overrides['is_filterable_in_search'] : 1,
		'used_in_product_listing'		=> isset($overrides['used_in_product_listing']) ? $overrides['used_in_product_listing'] : 1,
		'used_for_sort_by'				=> isset($overrides['used_for_sort_by']) ? $overrides['used_for_sort_by'] : 1,
		'is_configurable'				=> $configurable ? 1 : 0,
		'is_visible_in_advanced_search'	=> isset($overrides['is_visible_in_advanced_search']) ? $overrides['is_visible_in_advanced_search'] : 1,
		'is_used_for_promo_rules'		=> isset($overrides['is_used_for_promo_rules']) ? $overrides['is_used_for_promo_rules'] : 1 );
	$productEntityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
	$attr->setEntityTypeId($productEntityTypeId);
	
	$attr->addData($data);
	$attr->save();
	
	echo "Created ". ($configurable ? 'configurable' : 'normal') ." $backendType/$frontendInput attribute $code #{$attr->getId()}.\n";
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
function createSelectAttribute($code, $label, $configurable, $options, $overrides = array()) {
	echo "Create ". ($configurable ? 'configurable' : 'normal') ." attribute $code label: $label opts: ". join(', ', $options) ."\n";
	
	$attr = Mage::getModel('catalog/resource_eav_attribute');
	$data = array(
		'attribute_code'				=> $code,
		'backend_type'					=> 'int',
		'frontend_input'				=> 'select',
		'frontend_label'				=> $label,
		'is_required'					=> isset($overrides['is_required']) ? $overrides['is_required'] : 0,
		'is_user_defined'				=> 1,
		'default_value'					=> isset($overrides['default_value']) ? $overrides['default_value'] : 0,
		'is_unique'						=> isset($overrides['is_unique']) ? $overrides['is_unique'] : 0,
		'is_global'						=> isset($overrides['is_global']) ? $overrides['is_global'] : 1,
		'is_visible'					=> isset($overrides['is_visible']) ? $overrides['is_visible'] : 1,
		'is_searchable'					=> isset($overrides['is_searchable']) ? $overrides['is_searchable'] : 1,
		'is_filterable'					=> isset($overrides['is_filterable']) ? $overrides['is_filterable'] : 1,
		'is_comparable'					=> isset($overrides['is_comparable']) ? $overrides['is_comparable'] : 1,
		'is_visible_on_front'			=> isset($overrides['is_visible_on_front']) ? $overrides['is_visible_on_front'] : 1,
		'is_html_allowed_on_front'		=> isset($overrides['is_html_allowed_on_front']) ? $overrides['is_html_allowed_on_front'] : 1,
		'is_used_for_price_rules'		=> isset($overrides['is_used_for_price_rules']) ? $overrides['is_used_for_price_rules'] : 1,
		'is_filterable_in_search'		=> isset($overrides['is_filterable_in_search']) ? $overrides['is_filterable_in_search'] : 1,
		'used_in_product_listing'		=> isset($overrides['used_in_product_listing']) ? $overrides['used_in_product_listing'] : 1,
		'used_for_sort_by'				=> isset($overrides['used_for_sort_by']) ? $overrides['used_for_sort_by'] : 1,
		'is_configurable'				=> $configurable ? 1 : 0,
		'is_visible_in_advanced_search'	=> isset($overrides['is_visible_in_advanced_search']) ? $overrides['is_visible_in_advanced_search'] : 1,
		'is_used_for_promo_rules'		=> isset($overrides['is_used_for_promo_rules']) ? $overrides['is_used_for_promo_rules'] : 1 );
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

