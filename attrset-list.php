<?php
require_once 'init.php';

echo "List all product attribute sets\n";
$entityType = Mage::getModel('catalog/product')->getResource()->getEntityType();
$collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
	->setEntityTypeFilter($entityType->getId());
printf("%3s %-30s %s\n", "ID", "NAME", "DEFAULT GROUP");
foreach ($collection as $attributeSet) {
	$defaultGroupId = $attributeSet->getDefaultGroupId(); 
	$defaultGroup = Mage::getModel('eav/entity_attribute_group')->load($attributeSet->getDefaultGroupId());
	printf("%3d %-30s %3d %-20s\n", $attributeSet->getId(), $attributeSet->getAttributeSetName(),
		 $defaultGroupId, $defaultGroup->getAttributeGroupName());
	
	/** @var $groupCollection Mage_Eav_Model_Resource_Entity_Attribute_Group_Collection */
	$groupCollection    = Mage::getResourceModel('eav/entity_attribute_group_collection')
		->setAttributeSetFilter($attributeSet->getId())
		->setSortOrder()
		->load();
	foreach ($groupCollection as $group) {
		/* @var $group Mage_Eav_Model_Entity_Attribute_Group */
		$attrs = Mage::getResourceModel('catalog/product_attribute_collection')
			->setAttributeGroupFilter($group->getId())
			->addVisibleFilter()
			->checkConfigurableProducts();
		$attrCodes = array();
		foreach ($attrs as $attr) {
			$attrCodes[] = $attr->getAttributeCode();
		}
		printf("    %-20s: %s\n", $group->getAttributeGroupName(), join(' ', $attrCodes));
// 			if ($defaultGroupId == 0 or $group->getIsDefault()) {
// 				$defaultGroupId = $group->getId();
// 			}
	}
}
