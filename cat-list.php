#!/usr/bin/php
<?php
require_once 'init.php';

/**
 * "Website" is core/website
 * "Store" is core/store_group
 * "Store View" is core/store
 */
echo "List categories\n";

// if (is_null($parentId) && !is_null($store)) {
// 	$parentId = Mage::app()->getStore($this->_getStoreId($store))->getRootCategoryId();
// } elseif (is_null($parentId)) {
// 	$parentId = 1;
// }
$parentId = 1;

/* @var $tree Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Tree */
$tree = Mage::getResourceSingleton('catalog/category_tree')->load();

$root = $tree->getNodeById($parentId);

// if($root && $root->getId() == 1) {
// 	$root->setName(Mage::helper('catalog')->__('Root'));
// }

$collection = Mage::getModel('catalog/category')->getCollection()
// 	->setStoreId($this->_getStoreId($store))
	->addAttributeToSelect('*');
	//->addAttributeToSelect('is_active');

$tree->addCollectionData($collection, true);

// A=active. C=anchor
function printCategory($cat, $prefix) {
	/* @var $cat Mage_Catalog_Model_Category */
	printf("%2d %s%s %-20s %-12s %s%-30s %s\n", $cat->getId(), $cat->getIsActive() ? 'A' : '-',
		$cat->getIsAnchor() ? 'C' : '-',
		$cat->getUrlKey(), $cat->getDefaultSortBy(), $prefix, $cat->getName(), $cat->getAvailableSortBy() );
	//var_dump($cat);
	foreach ($cat->getChildren() as $child) {
		printCategory($child, '. '. $prefix);
	}
}

printCategory($root, '');
