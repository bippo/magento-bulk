#!/usr/bin/php
<?php
/**
 * Create a configurable product using 'Women Clothing' attribute set, that uses 'item_color' and 'item_size' configurable attributes.
 * The Attribute Set code is predefined.
 * The Attr
 */
require_once 'init.php';
require_once 'product_functions.php';

$opts = getopt('', array('sku:', 'name:', 'price:', 'cats:', 'weight:', 'store:', 'set:', 'summary:', 'desc:',
		'variants:', 'webs:'));
if (empty($opts) || empty($opts['sku']) || empty($opts['name']) || empty($opts['price']) || empty($opts['variants'])) {
	echo "Usage: product-add-conf.php --sku SKU --name NAME --price PRICE --variants COLOR/SIZE:QTY,... [--cats URL_KEY,...] [--summary SUMMARY] [--desc  DESCRIPTION] [--weight WEIGHT] [--store STORE_ID] [--set ATTRIBUTE_SET_ID] [--webs CODE,...]\n";
	exit(1);
}

echo 'Loading websites...';
$websites = Mage::getModel('core/website')->getCollection()->setLoadDefault(false);
$websiteLookup = array();
foreach ($websites as $website) {
	$websiteLookup[$website->getCode()] = $website->getWebsiteId();
}
echo 'x '. join(' ', array_keys($websiteLookup)) ."\n";

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
			$valueLookup[trim($optionValue['label'])] = $optionValue['value'];
			//echo "value-label : ".trim($optionValue['label'])." - ";
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
$cats = !empty($opts['cats']) ? $opts['cats'] : '';
$weight = !empty($opts['weight']) ? $opts['weight'] : 1.0;
$webs = !empty($opts['webs']) ? $opts['webs'] : join(',', array_keys($websiteLookup)); // if not specified, select all websites
echo "Create configurable product $sku name: $name set: $set price: $price variants: $variants cats: $cats webs: $webs\n";

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
	//var_dump($optionLookup['item_color']);
	echo "Varian Code Test => ". $variantCode."\n";
	$color = str_replace("_", " ", $color);
	echo "Test Color --> " . $optionLookup['item_color'][$color]."\n";
	if (!isset($optionLookup['item_color'][$color])) {
		throw new Exception("Cannot find option value for item_color '$color'");
	}
	if (!isset($optionLookup['item_size'][$size])) {
		throw new Exception("Cannot find option value for item_size '$size'");
	}
	$sizeId = $optionLookup['item_size'][$size];
	$variantSku = $sku .' - '. $color .'_'. $size;
	$variantName = $name .' - '. $color .'_'. $size;
	echo "Variant $variantSku $variantName: qty=$qty item_color=$colorId:$color item_size=$sizeId:$size\n";
	$variantsData[] = array(
			'sku' => $variantSku,
			'name' => $variantName,
			'qty' => $qty,
			'item_color' => $colorId,
			'item_size' => $sizeId
	);
}

// Create the childs products and configurable product
createConfigurableProduct(
	array(
		'item_color_attrId' => $item_color_attrId,
		'item_size_attrId'  => $item_size_attrId),
	array(
		'storeId'		=> $storeId,
		'setId'			=> $setId,
		'sku'			=> $sku,
		'name'			=> $name,
		'summary'		=> $summary,
		'description'	=> $description,
		'weight'		=> $weight,
		'price'			=> $price,
		'categoryIds'	=> $categoryIds,
		'websiteIds'	=> $websiteIds),
	$variantsData);
