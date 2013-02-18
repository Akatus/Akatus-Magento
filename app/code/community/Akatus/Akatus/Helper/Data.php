<?php
 
class Akatus_Akatus_Helper_Data extends Mage_Core_Helper_Abstract
{

    const MEIOS_PAGAMENTO           = "https://www.akatus.com/api/v1/meios-de-pagamento.json";
    const MEIOS_PAGAMENTO_SANDBOX   = "https://dev.akatus.com/api/v1/meios-de-pagamento.json";
    
    const CARRINHO          = "https://www.akatus.com/api/v1/carrinho.xml";
    const CARRINHO_SANDBOX  = "https://dev.akatus.com/api/v1/carrinho.xml";
    
    const BOLETO            = "https://www.akatus.com/boleto/";
    const BOLETO_SANDBOX    = "https://dev.akatus.com/boleto/";
    
    const TEF               = "https://www.akatus.com/tef/";
    const TEF_SANDBOX       = "https://dev.akatus.com/tef/";
    
    
    public static function getMeiosPagamentoUrl()
    {
        return self::_isSandboxMode() ? self::MEIOS_PAGAMENTO_SANDBOX : self::MEIOS_PAGAMENTO;
    }

    public static function getCarrinhoUrl()
    {
        return self::_isSandboxMode() ? self::CARRINHO_SANDBOX : self::CARRINHO;        
    }    

    public static function getBoletoUrl()
    {
        return self::_isSandboxMode() ? self::BOLETO_SANDBOX : self::BOLETO;
    }
    
    public static function getTefUrl()
    {
        return self::_isSandboxMode() ? self::TEF_SANDBOX : self::TEF;
    }        
    
    private static function _isSandboxMode()
    {
        if (Mage::getModel('akatus/pagar')->getConfigData('modo') === 'SANDBOX') {
            return true;
        }
        
        return false;
    }    
    
}
