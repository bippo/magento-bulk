#!/usr/bin/php
<?php
require_once 'lib/init.php';

echo "Delete all categories level 2 and below\n";

$categories = Mage::getModel('catalog/category')->getCollection()
	->addAttributeToSelect('*');
foreach ($categories as $cat) {
	if ($cat->getLevel() >= 2) {
		echo "Deleting #{$cat->getId()}:{$cat->getUrlKey()}:{$cat->getName()}...";
		$cat->delete();
		echo " DELETED.\n";
	} else {
		echo "Skipping #{$cat->getId()}:{$cat->getName()}...\n";
	}
}
