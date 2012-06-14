<?php
echo "Loading Magento...";
require_once 'config.php';
require_once MAGENTO_HOME . '/app/Mage.php';

Mage::init('admin');
Mage::app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_GLOBAL, Mage_Core_Model_App_Area::PART_EVENTS);
Mage::app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_ADMINHTML, Mage_Core_Model_App_Area::PART_EVENTS);
echo " OK.\n";
