<?php

/**
* Create category
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
*  @return int Product ID.
*/
function createCategory($storeId, $parentId, $urlkey, $name, $description, $metaTitle) {
	echo 'Parent category ID: '. $parentId ."\n";
	
	$parent = Mage::getModel('catalog/category')
		->setStoreId($storeId)
		->load($parentId);

	echo "Create category $urlkey $name in {$parent->getUrlkey()} store $storeId ...";
	
	/* @var $category Mage_Catalog_Model_Category */
	$category = Mage::getModel('catalog/category')
		->setStoreId($storeId);
	
	$category->setName($name);
	$category->setUrlKey($urlkey);
	if (!empty($description))
		$category->setDescription($description);
	if (!empty($metaTitle))
		$category->setMetaTitle($metaTitle);
	$category->setIsActive(1);
	$category->setIsAnchor(1);
	$category->setAttributeSetId($category->getDefaultAttributeSetId());
	$category->setParentId($parent->getId());
	$category->setIncludeInMenu(1);
	$category->setAvailableSortBy(array('position', 'name', 'price'));
	$category->setDefaultSortBy('position');
	$category->addData(array('path'=>implode('/', $parent->getPathIds())));
	
	$validate = $category->validate();
	if ($validate !== true) {
		foreach ($validate as $code => $error) {
			if ($error === true) {
				Mage::throwException(Mage::helper('catalog')->__('Attribute "%s" is required.', $code));
			} else {
				Mage::throwException($error);
			}
		}
	}
	
	$category->save();
	
	echo " #{$category->getId()}\n";
	return $category->getId();
}
