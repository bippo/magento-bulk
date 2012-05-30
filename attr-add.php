<?php
require_once 'init.php';

$args = getopt('', array('code:', 'label:', 'opts:'));
if (empty($args) || empty($args['code']) || empty($args['label']) || empty($args['opts'])) {
	echo "Create a select/dropdown configurable attribute\n";
	echo "Usage: php attr-add.php --code CODE --label LABEL --opts [OPT,OPT,...]\n";
	exit(1);
}

$code = $args['code'];
$label = $args['label'];
$opts = split(',', $args['opts']);
echo "Create attribute $code label: $label opts: ". join(', ', $opts) ."\n";

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
	'is_configurable' => 1,
	'is_visible_in_advanced_search' => 1,
	'is_used_for_promo_rules' => 1);
$productEntityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
$attr->setEntityTypeId($productEntityTypeId);

// Add options
$data['option'] = array('value' => array(), 'order' => array());
for ($i = 0; $i < count($opts); $i++) {
	$opt = $opts[$i];
	$placeholder_id = "option_" . ($i+1);
	$data['option']['value'][$placeholder_id] = array(0 => $opt);
	$data['option']['order'][$placeholder_id] = $i + 1;
}
// set default value
$data['default'] = array('option_1');

$attr->addData($data);
$attr->save();

echo "Created attribute #{$attr->getId()}.\n";
