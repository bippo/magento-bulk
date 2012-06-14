#!/usr/bin/php
<?php
require_once 'lib/init.php';
require_once 'lib/product_functions.php';

$opts = getopt('', array('sku:', 'name:', 'price:', 'qty:', 'cats:', 'weight:', 'store:', 'set:', 'summary:', 'desc:', 'webs:'));
if (empty($opts) || empty($opts['sku']) || empty($opts['name']) || empty($opts['price'])) {
	echo "Usage: product-add-simple.php --sku SKU --name NAME --price PRICE [--qty QTY] [--cats URL_KEY,...] [--summary SUMMARY] [--desc  DESCRIPTION] [--weight WEIGHT] [--store STORE_ID] [--set ATTRIBUTE_SET_ID] [--webs CODE,...]\n";
	exit(1);
}

echo 'Loading websites...';
$websites = Mage::getModel('core/website')->getCollection()->setLoadDefault(false);
$websiteLookup = array();
foreach ($websites as $website) {
	$websiteLookup[$website->getCode()] = $website->getWebsiteId();
}
echo ' '. join(' ', array_keys($websiteLookup)) ."\n";

echo 'Loading categories...';
$categories = Mage::getModel('catalog/category')->getCollection()
	->addAttributeToSelect('*');
$categoryLookup = array();
$defaultCategoryIds = array();
foreach ($categories as $cat) {
	if ($cat->getUrlKey() !== '')
		$categoryLookup[$cat->getUrlKey()] = $cat->getId();
	if ($cat->getLevel() <= 1 && $cat->getIsActive())
		$defaultCategoryIds[] = $cat->getId();
}
echo join(' ', array_keys($categoryLookup)) .". defaults: ". join(' ', $defaultCategoryIds) . "\n";

$sku = $opts['sku'];
$name = $opts['name'];
$price = $opts['price'];
$storeId = !empty($opts['store']) ? $opts['store'] : DEFAULT_STORE_ID;
$setId = !empty($opts['set']) ? $opts['set'] : DEFAULT_ATTRIBUTE_SET_ID;
$summary = !empty($opts['summary']) ? $opts['summary'] : $name;
$description = !empty($opts['desc']) ? $opts['desc'] : $summary;
$qty = !empty($opts['qty']) ? $opts['qty'] : 1.0;
$cats = !empty($opts['cats']) ? $opts['cats'] : '';
$weight = !empty($opts['weight']) ? $opts['weight'] : 1.0;
$webs = !empty($opts['webs']) ? $opts['webs'] : join(',', array_keys($websiteLookup)); // if not specified, select all websites
echo "Create simple product $sku name: $name price: $price qty: $qty cats: $cats webs: $webs\n";

$webCodes = split(',', $webs);
$websiteIds = array();
foreach ($webCodes as $webCode) {
	if (!isset($websiteLookup[$webCode]))
		throw new Exception("Cannot find website '$webCode'");
	$websiteIds[] = $websiteLookup[$webCode];
}
echo 'Website IDs: '. join(' ', $websiteIds) ."\n";

$catKeys = split(',', $cats);
$categoryIds = $defaultCategoryIds;
foreach ($catKeys as $catKey) {
	if (!isset($categoryLookup[$catKey]))
		throw new Exception("Cannot find category '$catKey'");
	$categoryId = $categoryLookup[$catKey];
	if (!in_array($categoryId, $categoryIds))
		$categoryIds[] = $categoryId;
}
echo 'Category IDs: '. join(' ', $categoryIds) ."\n";

createSimpleProduct(array(
	'storeId'		=> $storeId,
	'setId'			=> $setId,
	'sku'			=> $sku,
	'name'			=> $name,
	'summary'		=> $summary,
	'description'	=> $description,
	'weight'		=> $weight,
	'price'			=> $price,
	'categoryIds'	=> $categoryIds,
	'websiteIds'	=> $websiteIds));
