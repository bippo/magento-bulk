#!/usr/bin/php
<?php
require_once 'lib/init.php';

/**
 * "Website" is core/website
 * "Store" is core/store_group
 * "Store View" is core/store
 */
echo "List websites\n";
$websites = Mage::getModel('core/website')->getCollection()->setLoadDefault(true);
printf("%2s %-10s %s\n", "ID", "CODE", "NAME");
foreach ($websites as $website) {
	printf("%2d %-10s %-30s\n", $website->getWebsiteId(), $website->getCode(), $website->getName());
	
	$groups = Mage::getModel('core/store_group')->getCollection()->addWebsiteFilter($website->getWebsiteId());
	foreach ($groups as $group) {
		$stores = Mage::getModel('core/store')->getCollection()->addGroupFilter($group->getGroupId());
		$storeNames = array();
		foreach ($stores as $store) {
			$storeNames[] = $store->getStoreId() .':'. $store->getCode() .':'. $store->getName();
		}
		printf("   %d %-20s %d %d %s\n", $group->getGroupId(), $group->getName(),
			$group->getRootCategoryId(), $group->getDefaultStoreId(),
			join(' ', $storeNames));
	}
}
