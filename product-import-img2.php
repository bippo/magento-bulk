#!/usr/bin/php
<?php
/**
 * Import products with images, with additional attributes for Berbatik.
 * And using a new sub-category urlkey-path-based lookup mechanism (i.e. "children/clothing" instead of just "clothing")
 */
require_once 'lib/init.php';
require_once 'lib/product_functions.php';

/*
Sample input format:
 
<?xml version="1.0" encoding="UTF-8"?>
<commerce:ProductCatalog xmlns:commerce="http://commerce/1.0">
  <Product>
    <type>simple</type>
    <sku>batik_wirda_halim_lavita-fabric-30</sku>
    <slug>batik-wirda-halim-lavita-fabric-30</slug>
    <set>Fabric</set>
    <name>Lavita Fabric 30</name>
    <price>1050000</price>
    <categories>fabric,fabric/batik</categories>
    <weight>0.6</weight>
    <length>21.0</length>
    <height>20.0</height>
    <width>23.0</width>
    <description>Batik Yogyakarta adalah salah satu dari batik Indonesia yang pada awalnya dibuat terbatas hanya untuk kalangan keluarga keraton saja.
Setiap motif yang terujud dalam goresan canting pada kain batik Yogyakarta adalah sarat akan makna, adalah cerita.
 Hal inilah yang membedakan batik Yogyakarta dengan batik-batik lain, yang menjaga batik Yogyakarta tetap memiliki eksklusivitas dari sebuah mahakarya seni dan budaya Indonesia.</description>
    <imageFilesStr>berbatik-product-fabric/fabric05.jpg</imageFilesStr>
    <shopId>batik_wirda_halim</shopId>
    <localSku>lavita-fabric-30</localSku>
    <localPrice>875000</localPrice>
    <itemColor>Biru</itemColor>
    <material>Katun</material>
    <motif>Manuk Barunding</motif>
    <signature>OSK</signature>
    <batikTechnique>Tulis</batikTechnique>
    <origin>Indramayu</origin>
    <batikAge>Lawas</batikAge>
    <condition>Cacat</condition>
  </Product>
    ...
</commerce:ProductCatalog>

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
	echo "Import products with images and additional attributes\n";
	echo "Usage: product-import-img2.php INPUT_XML\n";
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
	/* @var $cat Mage_Catalog_Model_Category */
	if ($cat->getUrlKey() !== '') {
		$urlKeyPath = $cat->getUrlKey();
		// prepend parent url keys (if any)
		$current = $cat->getParentCategory();
		while ($current != null && $current->getLevel() >= 2 && $current->getUrlKey() != '') {
			$urlKeyPath = $current->getUrlKey() . '/' . $urlKeyPath; 
			$current = $current->getParentCategory();
		}
		// lookup using this urlkey-path
		$categoryLookup[$urlKeyPath] = $cat->getId();
	}
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
	
	// if it's a select attribute, map the options
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
	$productImage = (string)$product->images;
	
	// Determine website IDs
	$webCodes = !empty($webs) && $webs != '-' ? explode(',', $webs) : array();
	$websiteIds = array();
	var_dump($webCodes);
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
		$qty = (string)$product->qty;
		if ($qty == '') {
			echo "WARNING: $sku/$name has no qty! Setting to 1";
		}
		
		$additionalData = array();
		// Select attributes:
		//   batik_age, batik_technique, (color=not used), condition,
		//   item_color, item_size, leather, lining, (manufacturer=not used),
		//   material, motif, origin, shoe_size, signature
		if ($product->batikAge != '')
			$additionalData['batik_age'] = $optionLookup['batik_age'][(string)$product->batikAge];
		if ($product->batikTechnique != '')
			$additionalData['batik_technique'] = $optionLookup['batik_technique'][(string)$product->batikTechnique];
		if ($product->condition != '')
			$additionalData['condition'] = $optionLookup['condition'][(string)$product->condition];
		if ($product->item_color != '')
			$additionalData['item_color'] = $optionLookup['item_color'][(string)$product->itemColor];
		if ($product->item_size != '')
			$additionalData['item_size'] = $optionLookup['item_size'][(string)$product->itemSize];
		if ($product->leather != '')
			$additionalData['leather'] = $optionLookup['leather'][(string)$product->leather];
		if ($product->lining != '')
			$additionalData['lining'] = $optionLookup['lining'][(string)$product->lining];
		if ($product->material != '')
			$additionalData['material'] = $optionLookup['material'][(string)$product->material];
		if ($product->motif != '')
			$additionalData['motif'] = $optionLookup['motif'][(string)$product->motif];
		if ($product->origin != '')
			$additionalData['origin'] = $optionLookup['origin'][(string)$product->origin];
		if ($product->shoeSize != '')
			$additionalData['shoe_size'] = $optionLookup['shoe_size'][(string)$product->shoeSize];
		if ($product->signature != '')
			$additionalData['signature'] = $optionLookup['signature'][(string)$product->signature];
		
		// Literal attributes:
		//   bust_size, cost, dress_length, heels_height,
		//   width, height, length, local_price, local_sku,
		//   net_height, net_length, net_weight, net_width,
		//   shawl_length, shawl_width,
		//   shoe_measurement, shop_id, waist_size
		if ($product->bust_size != '')
			$additionalData['bust_size'] = (string)$product->signature;
		if ($product->cost != '')
			$additionalData['cost'] = (string)$product->cost;
		if ($product->dress_length != '')
			$additionalData['dress_length'] = (string)$product->dressLength;
		if ($product->heels_height != '')
			$additionalData['heels_height'] = (string)$product->heelsHeight;
		if ($product->width != '')
			$additionalData['width'] = (string)$product->width;
		if ($product->height != '')
			$additionalData['height'] = (string)$product->height;
		if ($product->length != '')
			$additionalData['length'] = (string)$product->length;
		if ($product->localPrice != '')
			$additionalData['local_price'] = (string)$product->localPrice;
		if ($product->localSku != '')
			$additionalData['local_sku'] = (string)$product->localSku;
		if ($product->netHeight != '')
			$additionalData['net_height'] = (string)$product->netHeight;
		if ($product->netLength != '')
			$additionalData['net_length'] = (string)$product->netLength;
		if ($product->netWeight != '')
			$additionalData['net_weight'] = (string)$product->netWeight;
		if ($product->netWidth!= '')
			$additionalData['net_width'] = (string)$product->netWidth;
		if ($product->shawlLength != '')
			$additionalData['shawl_length'] = (string)$product->shawlLength;
		if ($product->shawlWidth != '')
			$additionalData['shawl_width'] = (string)$product->shawlWidth;
		if ($product->shoeMeasurement != '')
			$additionalData['shoe_measurement'] = (string)$product->shoeMeasurement;
		if ($product->shopId != '')
			$additionalData['shop_id'] = (string)$product->shopId;
		if ($product->waistSize != '')
			$additionalData['waistSize'] = (string)$product->waistSize;

		var_dump($additionalData);
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
			'websiteIds'	=> $websiteIds,
			'qty'			=> 1),
			$additionalData);
		exit(0);
	} else if ($product->type == 'configurable') {
		echo "Create configurable product $sku name: $name set: $set price: $price variants: $variants cats: $cats webs: $webs\n";
		$variantCodes = explode(',', $variants);
		
		// Generate the child products
		$variantsData = array();
		foreach ($variantCodes as $variantCode) {
			if (!preg_match('/^(.+)\\/(.+):(.+)$/', $variantCode, $matches))
				throw new Exception("Invalid variant code: $variantCode");
			list($dummy, $color, $size, $qty) = $matches;
			$color = str_replace("_", " ", $color);
			if (!isset($optionLookup['item_color'][$color])) {
				throw new Exception("Cannot find option value for item_color '$color'");
			}
			$colorId = $optionLookup['item_color'][$color];
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
				'productImage'  => $productImage,
				'websiteIds'	=> $websiteIds),
			$variantsData);
	} else {
		throw new Exception("Unknown product type: {$product->type} for {$product->sku}\n");
	}
	
}
