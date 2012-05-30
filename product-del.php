#!/usr/bin/php
<?php
require_once 'init.php';

if (count($argv) < 2) {
	echo "Delete products\n";
	echo "Usage: product-del.php SKU [...]\n";
	exit(1);
}

for ($i = 1; $i < count($argv); $i++) {
	$sku = $argv[$i];
	echo "Deleting product $sku...";
	$product = Mage::getModel('catalog/product');
	$product->load($product->getIdBySku($sku));
	if ($product->getId() == false) {
		echo " Not Found!\n";
		continue;
	}
	$product->delete();
	echo " DELETED.\n";
}
