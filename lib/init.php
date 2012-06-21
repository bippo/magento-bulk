<?php
echo "Loading Magento...";
require_once dirname(__FILE__) . '/../config.php';

if (!defined('MAGENTO_HOME') || MAGENTO_HOME == '') {
	throw new Exception("Please define MAGENTO_HOME in config.php"); 
}

require_once MAGENTO_HOME . '/app/Mage.php';

Mage::init('admin');
Mage::app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_GLOBAL, Mage_Core_Model_App_Area::PART_EVENTS);
Mage::app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_ADMINHTML, Mage_Core_Model_App_Area::PART_EVENTS);
echo " OK.\n";
