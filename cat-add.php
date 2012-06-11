#!/usr/bin/php
<?php
require_once 'init.php';
require_once 'category_functions.php';

$args = getopt('', array('parent:', 'urlkey:', 'name:', 'title:', 'desc:'));
if (empty($args) || empty($args['urlkey']) || empty($args['name'])) {
	echo "Create sub-category\n";
	echo "Usage: cat-add.php --parent PARENT --urlkey URL_KEY --name NAME [--desc DESCRIPTION] [--title TITLE]\n";
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
$description = $args['desc'];
$metaTitle = $args['title'];
$store = 'default';
$storeId = 1;

if ($parent != '') {
	if (!isset($categoryLookup[$parent]))
		throw new Exception("Cannot find parent category '$parent'");
	$parentId = $categoryLookup[$parent];	
} else {
	$parentId = $defaultParentId;
}

$categoryId = createCategory($storeId, $parentId, $urlkey, $name, $description, $metaTitle);
