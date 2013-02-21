<?php
class Akatus_Akatus_Block_Form_Pay extends Mage_Payment_Block_Form
{
	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('akatus/form/pay.phtml');
	}
    
    public function getParcelamentoUrl()
    {
        return Akatus_Akatus_Helper_Data::getParcelamentoUrl();
    }
    
    public function getMeiosPagamentoUrl()
    {
        return Akatus_Akatus_Helper_Data::getMeiosPagamentoUrl();
    }
}