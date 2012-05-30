#!/usr/bin/php
<?php
/**
 * Create a configurable product using 'Women Clothing' attribute set, that uses 'item_color' and 'item_size' configurable attributes.
 * The Attribute Set code is predefined.
 * The Attr
 */
require_once 'init.php';

$opts = getopt('', array('sku:', 'name:', 'price:', 'cat:', 'weight:', 'store:', 'set:', 'summary:', 'desc:',
	'variants:'));
if (empty($opts) || empty($opts['sku']) || empty($opts['name']) || empty($opts['price']) || empty($opts['variants'])) {
	echo "Usage: product-add-conf.php --sku SKU --name NAME --price PRICE --variants COLOR/SIZE:QTY,... [--cat CATEGORY_ID] [--summary SUMMARY] [--desc  DESCRIPTION] [--weight WEIGHT] [--store STORE_ID] [--set ATTRIBUTE_SET_ID]\n";
	exit(1);
}

echo "Loading attribute sets...";
$entityType = Mage::getModel('catalog/product')->getResource()->getEntityType();
$collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
	->setEntityTypeFilter($entityType->getId());
$sets = array();
foreach ($collection as $attributeSet) {
	$sets[$attributeSet->getAttributeSetName()] = $attributeSet;
}
echo ' '. count($sets) . " found.\n";

echo "Loading user defined attributes...\n";
$udAttrs = Mage::getResourceModel('catalog/product_attribute_collection');
$udAttrs->addFieldToFilter('main_table.is_user_defined', 1);
$udAttrLookup = array();
$udAttrCodes = array();
$optionLookup = array();
foreach ($udAttrs as $attr) {
	$udAttrCodes[] = $attr->getAttributeCode();
	$udAttrLookup[$attr->getAttributeCode()] = $attr;
	if ($attr->usesSource()) {
		$valueLookup = array();
		$source = $attr->getSource();
		foreach ($source->getAllOptions() as $optionOrder => $optionValue) {
			if (empty($optionOrder) || empty($optionValue))
				continue;
			$valueLookup[$optionValue['label']] = $optionValue['value'];
		}
		$optionLookup[$attr->getAttributeCode()] = $valueLookup;
		echo $attr->getAttributeCode() .': '. join(' ', array_keys($valueLookup)) ."\n";
	}
}

$item_color_attrId = $udAttrLookup['item_color']->getId();
$item_size_attrId = $udAttrLookup['item_size']->getId();
echo "Attribute IDs: item_color=$item_color_attrId item_size=$item_size_attrId\n";

$sku = $opts['sku'];
$name = $opts['name'];
$price = $opts['price'];
$variants = !empty($opts['variants']) ? $opts['variants'] : '';
$storeId = !empty($opts['store']) ? $opts['store'] : DEFAULT_STORE_ID;
$set = !empty($opts['set']) ? $opts['set'] : 'Default';
$summary = !empty($opts['summary']) ? $opts['summary'] : $name;
$description = !empty($opts['desc']) ? $opts['desc'] : $summary;
$categoryId = !empty($opts['cat']) ? $opts['cat'] : DEFAULT_CATEGORY_ID;
$weight = !empty($opts['weight']) ? $opts['weight'] : 1.0;
echo "Create configurable product $sku name: $name set: $set price: $price variants: $variants qty: $qty\n";

if (!isset($sets[$set]))
	throw new Exception("Cannot find attribute set '$set'");
$setId = $sets[$set]->getId();
echo "Attribute set ID: $setId\n";

$variantCodes = split(',', $variants);

// Generate the child products
$variantsData = array();
foreach ($variantCodes as $variantCode) {
	if (!preg_match('/^(.+)\\/(.+):(.+)$/', $variantCode, $matches))
		throw new Exception("Invalid variant code: $variantCode");
	list($dummy, $color, $size, $qty) = $matches;
	if (!isset($optionLookup['item_color'][$color])) {
		throw new Exception("Cannot find option value for item_color '$color'");
	}
	$colorId = $optionLookup['item_color'][$color];
	if (!isset($optionLookup['item_size'][$size])) {
		throw new Exception("Cannot find option value for item_size '$size'");
	}
	$sizeId = $optionLookup['item_size'][$size];
	$variantSku = $sku .'-'. $color .'-'. $size;
	$variantName = $name .' '. $color .' '. $size;
	echo "Variant $variantSku $variantName: qty=$qty item_color=$colorId:$color item_size=$sizeId:$size\n";
	$variantsData[] = array(
		'sku' => $variantSku,
		'name' => $variantName,
		'qty' => $qty,
		'item_color' => $colorId,
		'item_size' => $sizeId
	);
}

// Create the child products
$variantIds = array(); // sku => magentoProductId
$configurableProductsData = array();
$configurableAttributesData = array(
	array('values' => array(), 'attribute_id' => $item_color_attrId, 'attribute_code' => 'item_color'),
	array('values' => array(), 'attribute_id' => $item_size_attrId, 'attribute_code' => 'item_size')
);
foreach ($variantsData as $variantData) {
	echo "Creating child product {$variantData['sku']}...";
	$product = Mage::getModel('catalog/product');
	$product->setStoreId($storeId)
		->setAttributeSetId($setId)
		->setTypeId('simple')
		->setSku($variantData['sku']);
	$product->setName($variantData['name']);
	$product->setShortDescription($summary);
	$product->setDescription($description);
	$product->setStatus(1);
	$product->setVisibility(1); // Not Visible Individually
	$product->setWeight($weight);
	$product->setPrice($price);
	$product->setCategoryIds(array($categoryId));
	$product->setTaxClassId(0); // 0=None 2=Taxable Goods 4=Shipping
	$product->addData(array(
		'item_color' => $variantData['item_color'],
		'item_size' => $variantData['item_size'] ));
	
	// set stock
	$stockData = array('qty' => $variantData['qty'], 'is_in_stock' => 1,
		'use_config_manage_stock' => 1, 'use_backorders' => 1);
	$product->setStockData($stockData);

	$product->save();
	echo " #{$product->getId()}.\n";
	
	$configurableProductsData[ $product->getId() ] = array();
	$configurableAttributesData[0]['values'][ $product->getId() ] = array(
		'attribute_id' => $item_color_attrId, 'value_index' => $variantData['item_color'] ); 
	$configurableAttributesData[1]['values'][ $product->getId() ] = array(
		'attribute_id' => $item_size_attrId, 'value_index' => $variantData['item_size'] ); 
}

// Create the parent configurable product
echo "Creating parent configurable product {$opts['sku']}...";

$product = Mage::getModel('catalog/product');
$product->setStoreId($storeId)
	->setAttributeSetId($setId)
	->setTypeId('configurable')
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

// set stock
$stockData = array('is_in_stock' => 1);
$product->setStockData($stockData);

// set configurable data
$product->setConfigurableProductsData($configurableProductsData);
$product->setConfigurableAttributesData($configurableAttributesData);

$product->save();

echo " #{$product->getId()}.\n";
