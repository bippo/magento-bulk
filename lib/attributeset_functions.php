<?php

/**
 * Create attribute set.
 * 
 * @param string $baseId Base attribute set ID.
 * @param string $name
 * @param array $attributeIds Attribute IDs to include in this set.
 * @return int Attribute Set ID
 */
function createAttributeSet($baseId, $name, $attributeIds) {
	echo "Create attribute set $name base ID: $baseId attribute IDs: ". join(' ', $attributeIds) ."\n";
	
	// get catalog product entity type id
	$entityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();
	/* @var $attributeSet Mage_Eav_Model_Entity_Attribute_Set */
	$attributeSet = Mage::getModel('eav/entity_attribute_set')
		->setEntityTypeId($entityTypeId)
		->setAttributeSetName($name);
	// check if name is valid
	$attributeSet->validate();
	// copy parameters to new set from skeleton set
	$attributeSet->save();
	$attributeSet->initFromSkeleton($baseId)->save();
	echo "Created attribute set #{$attributeSet->getId()}.\n";
	
	$sortOrder = 30;
	$attributeGroupId = $attributeSet->getDefaultGroupId();
	foreach ($attributeIds as $attrId) {
		echo "Adding attribute #{$attrId} order $sortOrder...";
		
		// check if attribute with requested id exists
		/* @var $attribute Mage_Eav_Model_Entity_Attribute */
		$attribute = Mage::getModel('eav/entity_attribute')->load($attrId);
		echo " ({$attribute->getAttributeCode()})";
		
		$attribute->setAttributeSetId($attributeSet->getId())->loadEntityAttributeIdBySet();
		$attribute->setEntityTypeId($attributeSet->getEntityTypeId())
			->setAttributeSetId($attributeSet->getId())
			->setAttributeGroupId($attributeGroupId)
			->setSortOrder($sortOrder)
			->save();
		echo " Added.\n";
		$sortOrder++;
	}
	
	return $attributeSet->getId();
}
