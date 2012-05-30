#!/usr/bin/php
<?php
require_once 'init.php';

echo "Delete all products\n";
$products = Mage::getModel('catalog/product')->getCollection();
foreach ($products as $product) {
	echo "Deleting #{$product->getSku()}: {$product->getSku()}...";
	$product->delete();
	echo " Deleted.\n";
}
