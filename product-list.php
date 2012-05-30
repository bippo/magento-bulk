<?php
require_once 'config.php';
require_once MAGENTO_HOME . '/app/Mage.php';

Mage::app();

echo "List all products\n";
$products = Mage::getModel('catalog/product')->getCollection();
foreach ($products as $product) {
	echo "#{$product->getSku()}: {$product->getSku()}\n";
}
