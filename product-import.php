#!/usr/bin/php
<?php
require_once 'init.php';
require_once 'product_functions.php';

/*
Sample input format:
 
<products>
    <product>
        <type>configurable</type>
        <sku>zibalabel_t03</sku>
        <set>Women Clothing</set>
        <!-- store is optional. default is 1 (usually the base website, default store view) -->
        <store>1</store> 
        <name>Tas Batik T-03</name>
        <price>38200.00</price>
        <categories>tas,handbag</categories>
        <!-- webs is optional -->
        <webs>base</webs>
        <weight>1.0</weight>
        <summary>Tas Trendy Banyak Gaya</summary>
        <description>Tas yang sangat praktis dipakai ke mana-mana.</description>
        <variants>Hijau/S:43,Hijau/M:20,Merah/S:12,Merah/M:15</variants>
    </product>
    ...
</products>

Output format:

<jobResult>
	<productAdd>
		<model>Product</model>
		<type>simple</type>
		<sku>zibalabel_t01-Hitam-Merah</sku>
		<productId>46</productId>
	</productAdd>
	<productAdd>
		<model>Product</model>
		<type>configurable</type>
		<sku>zibalabel_t01</sku>
		<productId>47</productId>
	</productAdd>
<jobResult>

In the future, the <variants> elements should also accept this format:

<variants>
	<variant>
		<item_color>Hijau</item_color>
		<item_size>S</item_size>
		<qty>43</qty>
	</variant>
	<variant>
		<item_color>Hijau</item_color>
		<item_size>M</item_size>
		<qty>20</qty>
	</variant>
	<variant>
		<item_color>Merah</item_color>
		<item_size>M</item_size>
		<qty>15</qty>
	</variant>
</variants>

or this dynamic style? :

<variants>
	<variant>
		<configurableAttribute name="item_color" value="Hijau"/>
		<configurableAttribute name="item_size" value="Hijau"/>
		<qty>43</qty>
	</variant>
	<variant>
		<configurableAttribute name="item_color" value="Hijau"/>
		<configurableAttribute name="item_size" value="M"/>
		<qty>20</qty>
	</variant>
	<variant>
		<configurableAttribute name="item_color" value="Merah"/>
		<configurableAttribute name="item_size" value="M"/>
		<qty>15</qty>
	</variant>
</variants>

*/

if (count($argv) < 2) {
	echo "Import products\n";
	echo "Usage: product-import.php INPUT_XML\n";
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
echo ' '. implode(', ', array_keys($sets)) ."\n";

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

// Load File XML
$xmlFilename = $argv[1];
echo "Loading $xmlFilename...";
$product_xml = simplexml_load_file($xmlFilename);
echo " Loaded.\n";
foreach ($product_xml as $product) {
	$storeId = !empty($product->store) ? (int)$product->store : DEFAULT_STORE_ID;
	$sku = (string)$product->sku;
	$name = (string)$product->name;
	$price = (double)$product->price;
	$variants = (string)$product->variants;
	$set = (string)$product->set;
	$weight = (double)$product->weight;
	$summary = (string)$product->summary;
	$description = (string)$product->description;
	$cats = (string)$product->categories;
	$webs = (string)$product->webs;
	
	// Determine website IDs
	$webCodes = !empty($webs) && $webs != '-' ? explode(',', $webs) : array();
	$websiteIds = array();
	foreach ($webCodes as $webCode) {
		if (!isset($websiteLookup[$webCode]))
			throw new Exception("Cannot find website '$webCode'");
		$websiteIds[] = $websiteLookup[$webCode];
	}
	echo 'Website IDs: '. join(' ', $websiteIds) ."\n";
	
	// Determine category IDs
	$catKeys = !empty($cats) && $cats != '-' ? explode(',', $cats) : array();
	$categoryIds = $defaultCategoryIds;
	foreach ($catKeys as $catKey) {
		if (!isset($categoryLookup[$catKey]))
			throw new Exception("Cannot find category '$catKey'");
		$categoryId = $categoryLookup[$catKey];
		if (!in_array($categoryId, $categoryIds))
			$categoryIds[] = $categoryId;
	}
	echo 'Category IDs: '. join(' ', $categoryIds) ."\n";

	// Determine attribute set ID
	if (!isset($sets[$set]))
		throw new Exception("Cannot find attribute set '$set'");
	$setId = $sets[$set]->getId();
	echo "Attribute set ID: $setId\n";

	if ($product->type == 'simple') {
		echo "Create simple product $sku name: $name price: $price qty: $qty cats: $cats webs: $webs\n";
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
	} else if ($product->type == 'configurable') {
		echo "Create configurable product $sku name: $name set: $set price: $price variants: $variants cats: $cats webs: $webs\n";
		
		$variantCodes = explode(',', $variants);
		
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
			$variantName = $name .'-'. $color .'-'. $size;
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
	} else {
		throw new Exception("Unknown product type: {$product->type} for {$product->sku}\n");
	}

}
