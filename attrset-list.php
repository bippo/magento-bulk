<?php
require_once 'init.php';

echo "List all product attribute sets\n";
$entityType = Mage::getModel('catalog/product')->getResource()->getEntityType();
$collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
	->setEntityTypeFilter($entityType->getId());
foreach ($collection as $attributeSet) {
	printf("%3d %-30s\n", $attributeSet->getId(), $attributeSet->getAttributeSetName() );
}
