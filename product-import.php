#!/usr/bin/php
<?php
require_once 'init.php';
require_once 'product_functions.php';

if (count($argv) < 2) {
	echo "Import products\n";
	echo "Usage: product-import.php INPUT_XML\n";
	exit(1);
}

// Load File XML
$xmlFilename = $argv[1];
echo "Loading $xmlFilename...";
$product_xml = simplexml_load_file($xmlFilename);
echo " Loaded.\n";
foreach ($product_xml as $product ) {
	$sku = $product->sku;
	$name = $product->name;
	$price = trim($product->price);
	$varian = $product->varian;
	$set = $product->set;
	$desc = $product->desciption;
	$summary = $product->summary;
	$cats = $product->categories;
	$website = $product->website;
		
	
	if ($cats == "-") {
		$setCategories = "";
	}  else {
		$setCategories = "--cats '".$cats."'";
	}
	
	if ($website == "-") {
		$setWebsite = "";
	}  else {
		$setWebsite = "--webs '".$website."'";
	}
		
	$cmd = "./product-add-conf.php --sku '".$sku."' --name '".$name."' --price '".$price."' --variants '".$varian."' --set '".$set."' ".$setCategories." --summary '".$summary."' --desc '".$desc."'".$setWebsite;
	echo shell_exec($cmd . '>> product-import-log.log');
}?>