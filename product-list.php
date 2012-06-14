#!/usr/bin/php
<?php
require_once 'lib/init.php';

echo "List all products with stock\n";
$products = Mage::getModel('catalog/product')->getCollection();
$types = array('simple' => 'S', 'configurable' => 'C', 'bundle' => 'B', 'virtual' => 'V', 'grouped' => 'G',
	'downloadable' => 'G');
$taxes = array(0 => 'N', 2 => 'T', 4 => 'S');
// tax_class_id: 2=Taxable Goods 4=Shipping
foreach ($products as $product) {
	$product->load();
	$stockItem = $product->getStockItem();
	$categoryIds = $product->getCategoryIds();
	$websiteIds = $product->getWebsiteIds();
	printf("%4d %1s %-25s %-40s %3d %s %2d %1s %-6s %-6s\n", $product->getId(), $types[ $product->getTypeId() ],
		$product->getSku(), $product->getName(),
		$stockItem->getQty(), $stockItem->getIsInStock() ? 'Y' : 'N',
		$product->getAttributeSetId(), $taxes[ $product->getTaxClassId() ],
		join(',', $categoryIds), join(',', $websiteIds) );
	if ($product->getTypeId() == 'configurable') {
// 		$attrData = $product->getConfigurableAttributesData();
// 		var_dump($attrData);
	}
}
