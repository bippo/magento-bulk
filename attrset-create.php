<?php
require_once 'init.php';

$opts = getopt('', array('name:', 'base:', 'attrs'));
if (empty($opts) || empty($opts['name'])) {
	echo "Usage: php attrset-create.php --name NAME [--base BASE_NAME] [--attrs ATTRIBUTE_CODE,...]\n";
	exit(1);
}

$name = $opts['name'];
$baseName = !empty($opts['base']) ? $opts['base'] : 'Default';
$attrs = !empty($opts['attrs']) ? $opts['attrs'] : '';
$attrCodes = split(',', $attrs);
echo "Create attribute set $name base: $baseName attrs: $attrs\n";

echo "Loading attribute sets...";
$entityType = Mage::getModel('catalog/product')->getResource()->getEntityType();
$collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
	->setEntityTypeFilter($entityType->getId());
$sets = array();
foreach ($collection as $attributeSet) {
	$sets[$attributeSet->getAttributeSetName()] = $attributeSet;
}
echo ' '. count($sets) . " found.\n";

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
