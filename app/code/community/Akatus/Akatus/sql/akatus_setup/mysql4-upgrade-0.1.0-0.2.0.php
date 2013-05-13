<?php

$installer = $this;
$installer->startSetup();

$prefix = Mage::getConfig()->getTablePrefix();

$query = "ALTER TABLE `".$prefix."sales_flat_order_payment`
				 DROP COLUMN `check_expiracaomes`,
                 DROP COLUMN `check_expiracaoano`,
                 DROP COLUMN `check_codseguranca`";


$installer->run($query);

$installer->endSetup();
