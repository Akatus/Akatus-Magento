<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * @category   Mage
 * @package    Akatus
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


$installer = $this;
/* @var $installer Akatus_Model_Mysql4_Setup */

$installer->startSetup();

$installer->run("CREATE TABLE IF NOT EXISTS `akatus_transacoes` (
`id` INT NULL AUTO_INCREMENT ,
`idpedido` INT NOT NULL ,
`codtransacao` VARCHAR( 255 ) NOT NULL ,
PRIMARY KEY ( `id` ) 
) ENGINE = InnoDB");

$prefix = Mage::getConfig()->getTablePrefix();

$query = "ALTER TABLE `".$prefix."sales_flat_order_payment`
				 ADD `check_no` VARCHAR(20) NOT NULL,
				 ADD `check_date` VARCHAR(20) NOT NULL,
				 ADD `check_cartaobandeira` VARCHAR(20) NOT NULL,
				 ADD `check_nome` VARCHAR(200) NOT NULL,
				 ADD `check_cpf` VARCHAR(30) NOT NULL,
			 	 ADD `check_numerocartao` VARCHAR(20) NOT NULL,
				 ADD `check_expiracaomes` VARCHAR(4) NOT NULL,
				 ADD `check_expiracaoano` VARCHAR(4) NOT NULL,
				 ADD `check_codseguranca` VARCHAR(5) NOT NULL,
				 ADD `check_parcelamento` VARCHAR(10) NOT NULL,
				 ADD `check_tefbandeira` VARCHAR(40) NOT NULL,
				 ADD `check_formapagamento` VARCHAR(40) NOT NULL,
				 ADD `check_boletourl` VARCHAR(200) NOT NULL";

$installer->run($query);


$installer->endSetup();
