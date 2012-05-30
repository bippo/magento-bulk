#!/usr/bin/php
<?php
require_once 'init.php';

if (count($argv) < 2) {
	echo "Delete products by ID\n";
	echo "Usage: product-delid.php ID [...]\n";
	exit(1);
}

for ($i = 1; $i < count($argv); $i++) {
	$id = $argv[$i];
	echo "Deleting product #$id...";
	$product = Mage::getModel('catalog/product');
	$product->load($id);
	if ($product->getId() == false) {
		echo " Not Found!\n";
		continue;
	}
	$product->delete();
	echo " DELETED.\n";
}
