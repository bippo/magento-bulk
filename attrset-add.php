<?php
require_once 'init.php';

$opts = getopt('', array('name:', 'base:', 'attrs:'));
if (empty($opts) || empty($opts['name'])) {
	echo "Usage: php attrset-add.php --name NAME [--base BASE_NAME] [--attrs ATTRIBUTE_CODE,...]\n";
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
	throw new Exception("Cannot find attribute set '$baseName");
}
$skeletonSetId = $sets[$baseName]->getId();

// get catalog product entity type id
$entityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();
/** @var $attributeSet Mage_Eav_Model_Entity_Attribute_Set */
$attributeSet = Mage::getModel('eav/entity_attribute_set')
	->setEntityTypeId($entityTypeId)
	->setAttributeSetName($name);
// check if name is valid
$attributeSet->validate();
// copy parameters to new set from skeleton set
$attributeSet->save();
$attributeSet->initFromSkeleton($skeletonSetId)->save();
echo "Created attribute set #{$attributeSet->getId()}.\n";

foreach ($attrCodes as $attrCode) {
	echo "Adding $attrCode...";
	if (!isset($udAttrLookup[$attrCode])) {
		echo "Attribute $attrCode not found, skipping!\n";
		continue;
	}
    // check if attribute with requested id exists
    /* @var $attribute Mage_Eav_Model_Entity_Attribute */
	$attribute = Mage::getModel('eav/entity_attribute')->load($udAttrLookup[$attrCode]->getId());
	
	$attributeGroupId = $attributeSet->getDefaultGroupId();
	$attribute->setAttributeSetId($attributeSet->getId())->loadEntityAttributeIdBySet();
	$sortOrder = 5;
	$attribute->setEntityTypeId($attributeSet->getEntityTypeId())
		->setAttributeSetId($attributeSet->getId())
		->setAttributeGroupId($attributeGroupId)
		->setSortOrder($sortOrder)
		->save();
	echo " Added.\n";
}
