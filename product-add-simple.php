<?php
require_once 'init.php';

$opts = getopt('', array('sku:', 'name:', 'price:', 'qty:', 'cat:', 'weight:', 'store:', 'set:', 'summary:', 'desc:'));
if (empty($opts) || empty($opts['sku']) || empty($opts['name']) || empty($opts['price'])) {
	echo "Usage: php product-add-simple.php --sku SKU --name NAME --price PRICE [--qty QTY] [--cat CATEGORY_ID] [--summary SUMMARY] [--desc  DESCRIPTION] [--weight WEIGHT] [--store STORE_ID] [--set ATTRIBUTE_SET_ID]\n";
	exit(1);
}

$sku = $opts['sku'];
$name = $opts['name'];
$price= $opts['price'];
$storeId = !empty($opts['store']) ? $opts['store'] : DEFAULT_STORE_ID;
$setId = !empty($opts['set']) ? $opts['set'] : DEFAULT_ATTRIBUTE_SET_ID;
$summary = !empty($opts['summary']) ? $opts['summary'] : $name;
$description = !empty($opts['desc']) ? $opts['desc'] : $summary;
$qty = !empty($opts['qty']) ? $opts['qty'] : 1.0;
$categoryId = !empty($opts['cat']) ? $opts['cat'] : DEFAULT_CATEGORY_ID;
$weight = !empty($opts['weight']) ? $opts['weight'] : 1.0;
echo "Create simple product $sku name: $name price: $price qty: $qty\n";

$product = Mage::getModel('catalog/product');
$product->setStoreId($storeId)
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
$product->setCategories(array($categoryId));

// set stock
$stockData = array('qty' => $qty, 'is_in_stock' => 1, 'use_config_manage_stock' => 1, 'use_backorders' => 1);
$product->setStockData($stockData);

$product->save();

echo "Created product #{$product->getId()}.\n";
