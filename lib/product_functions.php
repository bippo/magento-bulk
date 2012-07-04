<?php

/**
 * Create simple product
 *
 * @param array $productData Product data as map: storeId, setId, sku, name, summary, description, weight, price, categoryIds, websiteIds,
 *   Example: <pre>
 *   array(
 *     'storeId'		=> 1,
 *     'setId'			=> 9,
 *     'sku'			=> 'zibalabel_t01',
 *     'name'			=> 'Ziba Label T01',
 *     'summary'		=> 'Tas yang sangat bagus dan lucu',
 *     'description'	=> 'Cocok untuk dipakai belanja',
 *     'weight'			=> 5,
 *     'price'			=> 98000.0,
 *     'categoryIds'	=> array(4, 5),
 *     'websiteIds'		=> array(1, 2),
 *     'urlKey'			=> 'zibalabel-t01',
 *     'qty'			=> 1
 *   )</pre>
 * @param array $additionalData Additional data, for example values for user-defined attributes, e.g. cost.
 * @param array $imageFiles List of filenames of product images.
 * @return int Product ID.
 */
function createSimpleProduct($productData, $additionalData = array(), $imageFiles = array()) {
	// Read parameters
	$storeId = 0; //$productData['storeId']; // must be 0, otherwise upload images label and image,small_image,thumbnail cannot be set :(
	$setId = $productData['setId'];
	$sku = $productData['sku'];
	$name = $productData['name'];
	$summary = $productData['summary'];
	$description = $productData['description'];
	$weight = $productData['weight'];
	$price = $productData['price'];
	$categoryIds = $productData['categoryIds'];
	$websiteIds = $productData['websiteIds'];
	$urlKey = $productData['urlKey'];
	$qty = $productData['qty'];
	
	/* @var $product Mage_Catalog_Model_Product */
	$product = Mage::getModel('catalog/product');
	$product->setStoreId($storeId)		// is Product.storeId deprecated? seems weird, bcuz Product can be assigned to multiple Websites now
		->setAttributeSetId($setId)
		->setTypeId('simple')
		->setSku($sku);
	$product->setName($name);
	$product->setShortDescription($summary);
	$product->setDescription($description);
	$product->setStatus(1);
	$product->setVisibility(4);
	$product->setWeight($weight);
	$product->setPrice($price);
	$product->setCategoryIds($categoryIds);
	$product->setTaxClassId(0); // 0=None 2=Taxable Goods 4=Shipping
	$product->setWebsiteIds($websiteIds);
	$product->setUrlkey($urlKey);
	
	$product->addData($additionalData);

	// set stock
	$stockData = array('qty' => $qty, 'is_in_stock' => 1, 'use_config_manage_stock' => 1, 'use_backorders' => 1);
	$product->setStockData($stockData);

	foreach ($imageFiles as $imageFile) {
// 		$filename = $urlKey . '_' . basename($imageFile);
// 		$tmpFile = Mage::getBaseDir('media') . DS . 'import'. DS . $filename;
// 		echo "Copying $imageFile to $tmpFile...";
// 		copy($imageFile, $tmpFile);
// 		echo " COPIED\n";
		
		echo "Adding image $imageFile to $sku...";
		$productAttributes = $product->getTypeInstance(true)->getSetAttributes($product);
		if (!isset($productAttributes['media_gallery'])) {
			throw new Exception("Product $sku has no media_gallery attribute");
		}
		/* @var $gallery Mage_Catalog_Model_Resource_Eav_Attribute */
		$gallery = $productAttributes['media_gallery'];
		/* @var $galleryBackend Mage_Catalog_Model_Product_Attribute_Backend_Media */
		$galleryBackend = $gallery->getBackend();
		$image = $galleryBackend->addImage($product, $imageFile,
			array('image', 'small_image', 'thumbnail'), false, false);
// 		$image = $galleryBackend->addImage($product, $tmpFile,
// 			array('image', 'small_image', 'thumbnail'), true, false);
		echo ' '. $image;
		$galleryBackend->updateImage($product, $image, array(
			'label'				=> $sku . '-' . $name,
			'position'			=> 1,
			'exclude'			=> 0,
		));
// 		$galleryBackend->setMediaAttribute($product, array('image', 'small_image', 'thumbnail'), $image);
				
// 		$product->addImageToMediaGallery($imageFile, array('image', 'small_image', 'thumbnail'),
// 			false, false);
		
// 		// Set Attribute image
// 		$gallery = $product->getData('media_gallery');
// 		$lastImage = array_pop($gallery['images']); // the last added image (that is, the one added by the above code)
// 		$lastImage['label'] = $sku . ' - ' . $name;
// 		$lastImage['label_default'] = $sku . ' - ' . $name;
// 		$lastImage['position'] = 1;
// 		$lastImage['position_default'] = 1;
// // 		$lastImage['types'] = array('image', 'small_image', 'thumbnail');
// 		$lastImage['exclude'] = 0;
// 		$lastImage['exclude_default'] = 0;
// 		array_push($gallery['images'], $lastImage);
// 		var_dump($gallery);
// 		$product->setData('media_gallery', $gallery);

		echo " OK\n";
	}
	
	$product->save();

	echo "Created product #{$product->getId()}.\n";
	return $product->getId();
}

/**
 * Create configurable product with variants in item_color and item_size attributes.
 *
 * @param array $modelData Model data as map: item_color_attrId, item_size_attrId.
 *   Example: <code>array('item_color_attrId' => 123, 'item_size_attrId' => 145)</code>
 * @param array $productData Product data as map: storeId, setId, sku, name, summary, description, weight, price, categoryIds, websiteIds,
 *   Example: <pre>
 *   array(
 *     'storeId' => 1,
 *     'setId' => 9,
 *     'sku' => 'zibalabel_t01',
 *     'name' => 'Ziba Label T01',
 *     'summary' => 'Tas yang sangat bagus dan lucu',
 *     'description' => 'Cocok untuk dipakai belanja',
 *     'weight' => 5,
 *     'price' => 98000,
 *     'categoryIds' => array(4, 5),
 *     'websiteIds' => array(1, 2)
 *   )</pre>
 * @param array $variantsData Variant data as array. Each element is a map with sku, name, item_qty, item_size, and qty.
 *   Example: <pre>
 *   array(
 *     array('sku' => 'zibalabel_t01-Hitam-XL',
 *           'name' => 'Ziba Label T01-Hitam-XL',
 *           'item_color' => 'Hitam',
 *           'item_size' => 'XL',
 *           'qty' => 5),
 *     array('sku' => 'zibalabel_t01-Merah-L',
 *           'name' => 'Ziba Label T01-Merah-L',
 *           'item_color' => 'Merah',
 *           'item_size' => 'L',
 *           'qty' => 5)
 *  )</pre>
 *  @return array Product ID map with the form sku => ID.
 */
function createConfigurableProduct($modelData, $productData, $variantsData) {
	var_dump($modelData, $productData, $variantsData);
	// Read parameters
	$item_color_attrId = $modelData['item_color_attrId'];
	$item_size_attrId = $modelData['item_size_attrId'];
	$storeId = $productData['storeId'];
	$setId = $productData['setId'];
	$parentSku = $productData['sku'];
	$name = $productData['name'];
	$summary = $productData['summary'];
	$description = $productData['description'];
	$weight = $productData['weight'];
	$price = $productData['price'];
	$categoryIds = $productData['categoryIds'];
	$websiteIds = $productData['websiteIds'];
	$productImage = $productData['productImage'];

	// To hold results
	$result = array();

	// Create the child products
	$variantIds = array(); // sku => magentoProductId
	$configurableProductsData = array();
	$configurableAttributesData = array(
			array('attribute_id' => $item_color_attrId, 'attribute_code' => 'item_color', 'position' => 0, 'values' => array() ),
			array('attribute_id' => $item_size_attrId, 'attribute_code' => 'item_size', 'position' => 1, 'values' => array() )
	);
	foreach ($variantsData as $variantData) {
		echo "Creating child product {$variantData['sku']}...";
		/* @var $product Mage_Catalog_Model_Product */
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
		$product->setCategoryIds($categoryIds);
		$product->setTaxClassId(0); // 0=None 2=Taxable Goods 4=Shipping
		$product->setWebsiteIds($websiteIds);
		$product->addData(array(
				'item_color' => $variantData['item_color'],
				'item_size' => $variantData['item_size'] ));

		// set stock
		$stockData = array('qty' => $variantData['qty'], 'is_in_stock' => 1,
				'use_config_manage_stock' => 1, 'use_backorders' => 1);
		$product->setStockData($stockData);

		$product->save();
		echo " #{$product->getId()}.\n";

		$configurableProductsData[ $product->getId() ] = array(
				array('attribute_id' => $item_color_attrId, 'value_index' => $variantData['item_color'] ),
				array('attribute_id' => $item_size_attrId, 'value_index' => $variantData['item_size'] )
		);
		$configurableAttributesData[0]['values'][ $product->getId() ] = array(
				'value_index' => $variantData['item_color'] );
		$configurableAttributesData[1]['values'][ $product->getId() ] = array(
				'value_index' => $variantData['item_size'] );

		$result[ $variantData['sku'] ] = $product->getId();
	}

	// Create the parent configurable product
	echo "Creating parent configurable product {$parentSku}...";

	/* @var $product Mage_Catalog_Model_Product */
	$product = Mage::getModel('catalog/product');
	$product->setStoreId(0) //$storeId
		->setAttributeSetId($setId)
		->setTypeId('configurable')
		->setSku($parentSku);
	$product->setName($name);
	$product->setShortDescription($summary);
	$product->setDescription($description);
	$product->setStatus(1);
	$product->setVisibility(4);
	$product->setWeight($weight);
	$product->setPrice($price);
	$product->setCategoryIds($categoryIds);
	$product->setTaxClassId(0); // 0=None 2=Taxable Goods 4=Shipping
	$product->setWebsiteIds($websiteIds);

	// set stock
	$stockData = array('is_in_stock' => 1);
	$product->setStockData($stockData);
	
	// Set image
	if ($productImage != '' && $productImage != "-") {
// 		$image_url = $productImage;
// 		$image_type = substr(strrchr($image_url,"."), 1);
// 		$filename = md5($image_url) . '.'. $image_type;
// 		$filepath = Mage::getBaseDir('media') . DS . 'import'. DS . $filename;
// 		file_put_contents($filepath, file_get_contents(trim($image_url)));
// 		$product->addImageToMediaGallery($filepath, array('image', 'small_image', 'thumbnail'), true, false);

		$image_url = $productImage;
		$path_img = IMG_PATH.$catalogImg;
		$product->addImageToMediaGallery($path_img, array('image', 'small_image', 'thumbnail'), false, false);
		
		// Set Attribute image
		$gallery = $product->getData('media_gallery');
		$lastImage = array_pop($gallery['images']);
		$lastImage['label'] = $parentSku . '-' . $name;
		$lastImage['position'] = 1;
		$lastImage['types'] = array('image', 'small_image', 'thumbnail');
		$lastImage['exclude'] = 0;
		array_push($gallery['images'], $lastImage);
		$product->setData('media_gallery', $gallery);
	}

	// set configurable data
	$product->setConfigurableProductsData($configurableProductsData);
	$product->setConfigurableAttributesData($configurableAttributesData);
	
	// Save product
	$product->save();
	
	echo " #{$product->getId()}.\n";
	$result[ $parentSku ] = $product->getId();
	return $result;
}
