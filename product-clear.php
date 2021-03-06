#!/usr/bin/php
<?php
require_once 'lib/init.php';

echo "Delete all products\n";
$products = Mage::getModel('catalog/product')->getCollection();
foreach ($products as $product) {
	echo "Deleting #{$product->getId()}: {$product->getSku()}...";
	$product->delete();
	echo " Deleted.\n";
}
