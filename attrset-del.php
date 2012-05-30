#!/usr/bin/php
<?php
require_once 'init.php';

if (count($argv) < 2) {
	echo "Delete attribute set\n";
	echo "Usage: attrset-del.php NAME [...]\n";
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

for ($i = 1; $i < count($argv); $i++) {
	$name = $argv[$i];
	echo "Deleting attribute set '$name'...";
	if (!isset($sets[$name])) {
		echo " Not Found!\n";
		continue;
	}
	$sets[$name]->delete();
	echo " DELETED.\n";
}
