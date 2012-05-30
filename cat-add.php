#!/usr/bin/php
<?php
require_once 'init.php';

$args = getopt('', array('parent:', 'urlkey:', 'name:'));
if (empty($args) || empty($args['urlkey']) || empty($args['name'])) {
	echo "Create sub-category\n";
	echo "Usage: cat-add.php --parent PARENT --urlkey URL_KEY --name NAME\n";
	exit(1);
}

$defaultParentId = Mage::app()->getDefaultStoreView()->getRootCategoryId();

echo 'Loading categories...';
$categories = Mage::getModel('catalog/category')->getCollection()
	->addAttributeToSelect('*');
$categoryLookup = array();
foreach ($categories as $cat) {
	if ($cat->getUrlKey() !== '')
		$categoryLookup[$cat->getUrlKey()] = $cat->getId();
}
echo join(' ', array_keys($categoryLookup)) .". default: ". $defaultParentId . "\n";

$parent = $args['parent'];
$urlkey = $args['urlkey'];
$name = $args['name'];
$store = 'default';
$storeId = 0;

if ($parent != '') {
	if (!isset($categoryLookup[$parent]))
		throw new Exception("Cannot find parent category '$parent'");
	$parentId = $categoryLookup[$parent];	
} else {
	$parentId = $defaultParentId;
}
echo 'Parent category ID: '. $parentId ."\n";

$parent_category = Mage::getModel('catalog/category')
	->setStoreId($storeId)
	->load($parentId);

echo "Create category $urlkey $name in $parent store $store...";

/* @var $category Mage_Catalog_Model_Category */
$category = Mage::getModel('catalog/category')
	->setStoreId($storeId);

$category->setName($name);
$category->setUrlKey($urlkey);
$category->setIsActive(1);
$category->setIsAnchor(1);
$category->setAttributeSetId($category->getDefaultAttributeSetId());
$category->setParentId($parent_category->getId());
$category->setIncludeInMenu(1);
$category->setAvailableSortBy(array('position', 'name', 'price'));
$category->setDefaultSortBy('position');
$category->addData(array('path'=>implode('/', $parent_category->getPathIds())));

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
