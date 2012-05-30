<?php
require_once 'init.php';

echo "List all products with stock\n";
$products = Mage::getModel('catalog/product')->getCollection();
foreach ($products as $product) {
	$product->load();
	$stockItem = $product->getStockItem();
	printf("%3d %-12s %-20s %-40s %3d %s\n", $product->getId(), $product->getTypeId(), $product->getSku(), $product->getName(),
		$stockItem->getQty(), $stockItem->getInStock() ? 'Y' : 'N');
}
