<?php

$installer = $this;

$installer->startSetup();


$prefix = Mage::getConfig()->getTablePrefix();


$installer->run("CREATE TABLE IF NOT EXISTS `akatus_transacoes` ( `id` INT NULL AUTO_INCREMENT ,
                                                                  `idpedido` INT NOT NULL ,
                                                                  `codtransacao` VARCHAR( 255 ) NOT NULL ,
                                                                  PRIMARY KEY ( `id` ) 
                                                                  ) ENGINE = InnoDB");


$installer->run($query);

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
