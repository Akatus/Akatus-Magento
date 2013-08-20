<?php

$installer = $this;
$installer->startSetup();

$prefix = Mage::getConfig()->getTablePrefix();

$query = "INSERT INTO `".$prefix."sales_order_status` VALUES ('refunded', 'Estornado')";
$installer->run($query);

$query = "INSERT INTO `".$prefix."sales_order_status_state` VALUES ('refunded', 'refunded', 0)";
$installer->run($query);


$installer->endSetup();
