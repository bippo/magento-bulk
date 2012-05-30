#!/usr/bin/php
<?php
require_once 'init.php';

if (count($argv) < 2) {
	echo "Delete attributes\n";
	echo "Usage: attr-del.php CODE [...]\n";
	exit(1);
}

for ($i = 1; $i < count($argv); $i++) {
	$code = $argv[$i];
	echo "Deleting attribute $code...";
	$attr = Mage::getModel ( 'catalog/product' )->setStoreId(0)->getResource()->getAttribute($code);
	if (empty($attr)) {
		echo " Not Found!\n";
		continue;
	}
	$attr->delete();
	echo " DELETED.\n";
}
