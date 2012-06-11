#!/usr/bin/php
<?php
require_once 'init.php';
require_once 'category_functions.php';

if (count($argv) < 2) {
	echo "Import categories\n";
	echo "Usage: cat-import.php INPUT_XML\n";
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

// Load File XML
$xmlFilename = $argv[1];
echo "Loading $xmlFilename...";
$categoriesXml = simplexml_load_file($xmlFilename);
echo " Loaded.\n";
foreach ($categoriesXml as $categoryEl) {
	$parent = (string) $categoryEl->parentUrlKey;
	$urlkey = (string) $categoryEl->urlKey;
	$name = (string) $categoryEl->name;
	$description = (string) $categoryEl->description;
	$metaTitle = (string) $categoryEl->metaTitle;
	
	if ($parent != '') {
		if (!isset($categoryLookup[$parent]))
		throw new Exception("Cannot find parent category '$parent'");
		$parentId = $categoryLookup[$parent];
	} else {
		$parentId = $defaultParentId;
	}
	echo "Parent category for $urlkey is $parent (#$parentId)\n";
	
	$categoryId = createCategory($parentId, $urlkey, $name, $description, $metaTitle);
	
	// update categoryLookup to be used by next elements
	$categoryLookup[$urlkey] = $categoryId;
}
