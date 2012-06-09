<?php

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
 */
function createConfigurableProduct($modelData, $productData, $variantsData) {
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
	
	// Create the child products
	$variantIds = array(); // sku => magentoProductId
	$configurableProductsData = array();
	$configurableAttributesData = array(
		array('attribute_id' => $item_color_attrId, 'attribute_code' => 'item_color', 'position' => 0, 'values' => array() ),
		array('attribute_id' => $item_size_attrId, 'attribute_code' => 'item_size', 'position' => 1, 'values' => array() )
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
	}

	// Create the parent configurable product
	echo "Creating parent configurable product {$parentSku}...";

	$product = Mage::getModel('catalog/product');
	$product->setStoreId($storeId)
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

	// set configurable data
	$product->setConfigurableProductsData($configurableProductsData);
	$product->setConfigurableAttributesData($configurableAttributesData);

	$product->save();

	echo " #{$product->getId()}.\n";
}