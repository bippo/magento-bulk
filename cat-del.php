#!/usr/bin/php
<?php
require_once 'init.php';

if (count($argv) < 2) {
	echo "Delete categories by URL Key\n";
	echo "Usage: cat-del.php URL_KEY [...]\n";
	exit(1);
}

echo 'Loading categories...';
$categories = Mage::getModel('catalog/category')->getCollection()
	->addAttributeToSelect('*');
$categoryLookup = array();
foreach ($categories as $cat) {
	if ($cat->getUrlKey() !== '')
		$categoryLookup[$cat->getUrlKey()] = $cat;
}
echo join(' ', array_keys($categoryLookup)) ."\n";

for ($i = 1; $i < count($argv); $i++) {
	$urlkey = $argv[$i];
	echo "Deleting category $urlkey...";
	if (!isset($categoryLookup[$urlkey])) {
		echo " Not Found!\n";
		continue;
	}
	$categoryLookup[$urlkey]->delete();
	echo " DELETED.\n";
}
