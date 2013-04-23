<?php
 
class Akatus_Akatus_Helper_Data extends Mage_Core_Helper_Abstract
{

    const MEIOS_PAGAMENTO           = "https://www.akatus.com/api/v1/meios-de-pagamento.json";
    const MEIOS_PAGAMENTO_SANDBOX   = "https://dev.akatus.com/api/v1/meios-de-pagamento.json";
    
    const PARCELAMENTO              = "https://www.akatus.com/api/v1/parcelamento/simulacao.json?email={EMAIL}&amount={AMOUNT}&payment_method=cartao_master&api_key={API_KEY}";
    const PARCELAMENTO_SANDBOX      = "https://dev.akatus.com/api/v1/parcelamento/simulacao.json?email={EMAIL}&amount={AMOUNT}&payment_method=cartao_master&api_key={API_KEY}";
    
    const ESTORNO           = "https://www.akatus.com/api/v1/estornar-transacao.xml";
    const ESTORNO_SANDBOX   = "https://dev.akatus.com/api/v1/estornar-transacao.xml";
    
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

    public static function getParcelamentoUrl()
    {
        return self::_isSandboxMode() ? self::PARCELAMENTO_SANDBOX : self::PARCELAMENTO;
    }

    public static function getEstornoUrl()
    {
        return self::_isSandboxMode() ? self::ESTORNO_SANDBOX : self::ESTORNO;
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
    
	public static function xml2array($contents, $get_attributes=1) {
        if (!$contents)
                return array();

        if (!function_exists('xml_parser_create')) {
                return array();
        }

        //Get the XML parser of PHP - PHP must have this module for the parser to work
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, $contents, $xml_values);
        xml_parser_free($parser);

        if (!$xml_values)
                return; //Hmm...
                
		//Initializations

        $xml_array = array();
        $parents = array();
        $opened_tags = array();
        $arr = array();

        $current = &$xml_array;

        //Go through the tags.

        foreach ($xml_values as $data) {
                unset($attributes, $value); //Remove existing values, or there will be trouble
                extract($data); //We could use the array by itself, but this cooler.

                $result = '';

                if ($get_attributes) {//The second argument of the function decides this.
                        $result = array();
                        if (isset($value))
                                $result['value'] = $value;

                        //Set the attributes too.
                        if (isset($attributes)) {
                                foreach ($attributes as $attr => $val) {
                                        if ($get_attributes == 1)
                                                $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                                }
                        }
                } elseif (isset($value)) {
                        $result = $value;
                }

                //See tag status and do the needed.

                if ($type == "open") {//The starting of the tag '<tag>'
                        $parent[$level - 1] = &$current;

                        if (!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                                $current[$tag] = $result;
                                $current = &$current[$tag];
                        } else { //There was another element with the same tag name
                                if (isset($current[$tag][0])) {
                                        array_push($current[$tag], $result);
                                } else {
                                        $current[$tag] = array($current[$tag], $result);
                                }
                                $last = count($current[$tag]) - 1;
                                $current = &$current[$tag][$last];
                        }
                } elseif ($type == "complete") { //Tags that ends in 1 line '<tag />'
                        //See if the key is already taken.
                        if (!isset($current[$tag])) { //New Key
                                $result = str_replace('|', '&', $result);
                                $current[$tag] = $result;
                        } else { //If taken, put all things inside a list(array)
                                if ((is_array($current[$tag]) and $get_attributes == 0)//If it is already an array...
                                        or (isset($current[$tag][0]) and is_array($current[$tag][0]) and $get_attributes == 1)) {
                                        array_push($current[$tag], $result); // ...push the new element into that array.
                                } else { //If it is not an array...
                                        $current[$tag] = array($current[$tag], $result); //...Make it an array using using the existing value and the new value
                                }
                        }
                } elseif ($type == 'close') { //End of tag '</tag>'
                        $current = &$parent[$level - 1];
                }
        }

        if (!empty($xml_array['root']['node']['id'])) {
                $return['root']['node'][0] = $xml_array['root']['node'];
        } else {
                $return = $xml_array;
        }
        return($return);
	}    
}