<?php

/**
* Create category
*
* @param int $parentId Parent category ID.
* @param string $urlkey URL Key.
* @param string $name Name.
* @param string $description Description.
* @param string $metaTitle Meta title. 
*  @return int Category ID.
*/
function createCategory($parentId, $urlkey, $name, $description, $metaTitle) {
	// Sanity checks
	if ($urlkey == '')
		throw new Exception("Please provide urlkey");
	if ($name == '')
		throw new Exception("Please provide name");
	
	// This must be 0. Otherwise you won't be able to set description, and probably won't work properly.
	$storeId = 0;
	
	$parent = Mage::getModel('catalog/category')
		->setStoreId($storeId)
		->load($parentId);

	echo "Create category $urlkey $name in {$parent->getUrlKey()} ($parentId) ...";
	
	/* @var $category Mage_Catalog_Model_Category */
	$category = Mage::getModel('catalog/category')
		->setStoreId($storeId);
	
	$category->setName($name);
	$category->setUrlKey($urlkey);
	if (!empty($description))
		$category->setDescription($description);
	if (!empty($metaTitle))
		$category->setMetaTitle($metaTitle);
	$category->setIsActive(1);
	$category->setIsAnchor(1);
	$category->setAttributeSetId($category->getDefaultAttributeSetId());
	$category->setParentId($parent->getId());
	$category->setIncludeInMenu(1);
	$category->setAvailableSortBy(array('position', 'name', 'price'));
	$category->setDefaultSortBy('position');
	$category->addData(array('path'=>implode('/', $parent->getPathIds())));
	
	$validate = $category->validate();
	if ($validate !== true) {
		foreach ($validate as $code => $error) {
			if ($error === true) {
				Mage::throwException(Mage::helper('catalog')->__('Attribute "%s" is required.', $code));
			} else {
				Mage::throwException($error);
			}
		}
	}
	
	$category->save();
	
	echo " #{$category->getId()}\n";
	return $category->getId();
}
