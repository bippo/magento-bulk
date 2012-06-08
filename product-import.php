#!/usr/bin/php
<?php
// Load File XML
$product_xml = simplexml_load_file("/home/agus/git/tuneeca-migration/product-migration-conf-all-v2/data/product-xml.xml");
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