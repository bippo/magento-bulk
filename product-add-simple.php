#!/usr/bin/php
<?php
require_once 'init.php';

$opts = getopt('', array('sku:', 'name:', 'price:', 'qty:', 'cat:', 'weight:', 'store:', 'set:', 'summary:', 'desc:', 'webs:'));
if (empty($opts) || empty($opts['sku']) || empty($opts['name']) || empty($opts['price'])) {
	echo "Usage: product-add-simple.php --sku SKU --name NAME --price PRICE [--qty QTY] [--cat CATEGORY_ID] [--summary SUMMARY] [--desc  DESCRIPTION] [--weight WEIGHT] [--store STORE_ID] [--set ATTRIBUTE_SET_ID] [--webs CODE,...]\n";
	exit(1);
}

echo 'Loading websites...';
$websites = Mage::getModel('core/website')->getCollection()->setLoadDefault(false);
$websiteLookup = array();
foreach ($websites as $website) {
	$websiteLookup[$website->getCode()] = $website->getWebsiteId();
}
echo ' '. join(' ', array_keys($websiteLookup)) ."\n";

$sku = $opts['sku'];
$name = $opts['name'];
$price = $opts['price'];
$storeId = !empty($opts['store']) ? $opts['store'] : DEFAULT_STORE_ID;
$setId = !empty($opts['set']) ? $opts['set'] : DEFAULT_ATTRIBUTE_SET_ID;
$summary = !empty($opts['summary']) ? $opts['summary'] : $name;
$description = !empty($opts['desc']) ? $opts['desc'] : $summary;
$qty = !empty($opts['qty']) ? $opts['qty'] : 1.0;
$categoryId = !empty($opts['cat']) ? $opts['cat'] : DEFAULT_CATEGORY_ID;
$weight = !empty($opts['weight']) ? $opts['weight'] : 1.0;
$webs = !empty($opts['webs']) ? $opts['webs'] : join(',', array_keys($websiteLookup)); // if not specified, select all websites
echo "Create simple product $sku name: $name price: $price qty: $qty webs: $webs\n";

$webCodes = split(',', $webs);
$websiteIds = array();
foreach ($webCodes as $webCode) {
	if (!isset($websiteLookup[$webCode]))
		throw new Exception("Cannot find website '$webCode'");
	$websiteIds[] = $websiteLookup[$webCode];
}

$product = Mage::getModel('catalog/product');
$product->setStoreId($storeId)		// is Product.storeId deprecated? seems weird, bcuz Product can be assigned to multiple Websites now 
	->setAttributeSetId($setId)
	->setTypeId('simple')
	->setSku($opts['sku']);
$product->setName($name);
$product->setShortDescription($summary);
$product->setDescription($description);
$product->setStatus(1);
$product->setVisibility(4);
$product->setWeight($weight);
$product->setPrice($price);
$product->setCategoryIds(array($categoryId));
$product->setTaxClassId(0); // 0=None 2=Taxable Goods 4=Shipping
$product->setWebsiteIds($websiteIds);

// set stock
$stockData = array('qty' => $qty, 'is_in_stock' => 1, 'use_config_manage_stock' => 1, 'use_backorders' => 1);
$product->setStockData($stockData);

$product->save();

echo "Created product #{$product->getId()}.\n";
