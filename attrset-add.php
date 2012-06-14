#!/usr/bin/php
<?php
require_once 'lib/init.php';
require_once 'lib/attributeset_functions.php';

$opts = getopt('', array('name:', 'base:', 'attrs:'));
if (empty($opts) || empty($opts['name'])) {
	echo "Usage: attrset-add.php --name NAME [--base BASE_NAME] [--attrs ATTRIBUTE_CODE,...]\n";
	exit(1);
}

echo "Loading attribute sets...";
$entityType = Mage::getModel('catalog/product')->getResource()->getEntityType();
$collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
	->setEntityTypeFilter($entityType->getId());
$sets = array();
foreach ($collection as $attributeSet) {
	$sets[$attributeSet->getAttributeSetName()] = $attributeSet;
}
echo ' '. count($sets) . " found.\n";

echo "Loading user defined attributes...";
$udAttrs = Mage::getResourceModel('catalog/product_attribute_collection');
$udAttrs->addFieldToFilter('main_table.is_user_defined', 1);
$udAttrLookup = array();
$udAttrCodes = array();
foreach ($udAttrs as $attr) {
	$udAttrCodes[] = $attr->getAttributeCode();
	$udAttrLookup[$attr->getAttributeCode()] = $attr;
}
echo ' '. join(' ', $udAttrCodes) ."\n";

$name = $opts['name'];
$baseName = !empty($opts['base']) ? $opts['base'] : 'Default';
$attrs = !empty($opts['attrs']) ? $opts['attrs'] : '';
$attrCodes = split(',', $attrs);
echo "Create attribute set $name base: $baseName attrs: $attrs\n";

// check if set with requested $skeletonSetId exists
if (!isset($sets[$baseName])) {
	throw new Exception("Cannot find base attribute set '$baseName'");
}
$skeletonSetId = $sets[$baseName]->getId();

$attributeIds = array();
foreach ($attrCodes as $attrCode) {
	echo "Lookup $attrCode...";
	if (!isset($udAttrLookup[$attrCode])) {
		echo "Attribute $attrCode not found, skipping!\n";
		continue;
	}
	$attributeId = $udAttrLookup[$attrCode]->getId();
	echo " $attributeId\n";
	$attributeIds[] = $attributeId;
}

createAttributeSet($skeletonSetId, $name, $attributeIds);
