<?php
 
class Akatus_Akatus_Model_Pagar extends Mage_Payment_Model_Method_Abstract
{
	protected $_formBlockType = 'akatus/form_pay';
	protected $_infoBlockType = 'akatus/info_pay';
        
    /**
    * Identificacao do metodo de pagamento 
    *
    * @var string [a-z0-9_]
    */
    protected $_code = 'akatus';
 
    /**
     * Abaixo algumas flags qua vao determinar os recursos disponiveis e o comportamento
     * deste modulo
     * @see todas as flags disponiveis in Mage_Payment_Model_Method_Abstract
     */
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc               = false;
    protected $_isInitializeNeeded      = true;

    function isTelephoneValid($tel){
        $valid = true;

        $telSoNumeros = preg_replace('([^0-9])','',$tel);
        $size = strlen($telSoNumeros);
        
        if($size < 10 || $size > 11){
            $valid = false;
        }
        
        if(!$valid) {
            $errorMsg = $this->_getHelper()->__('Telefone inválido. Deve ser informado o código de área com 2 dígitos seguido do número do telefone com 8 ou 9 dígitos, e somente números (Ex.: 1199999999).');
            Mage::throwException($errorMsg);
        }
    }
    
    function stringToUf($estado){
        
        $uf = "";

        switch ($estado) {
            case 'Acre':
            case 'AC':
                $uf = 'AC';
                break;
            case 'Alagoas':
            case 'AL':
                $uf = 'AL';
                break;
            case 'Amazonas':
            case 'AM':
                $uf = 'AM';
                break;
            case 'Amapá':
            case 'Amapa':
            case 'AP':
                $uf = 'AP';
                break;
            case 'Bahia':
            case 'BA':
                $uf = 'BA';
                break;
            case 'Ceará':
            case 'Ceara':
            case 'CE':
                $uf = 'CE';
                break;
            case 'Distrito Federal':
            case 'DF':
                $uf = 'DF';
                break;
            case 'Espírito Santo':
            case 'Espirito Santo':
            case 'ES':
                $uf = 'ES';
                break;
            case 'Goiás':
            case 'Goias':
            case 'GO':
                $uf = 'GO';
                break;
            case 'Maranhão':
            case 'Maranhao':
            case 'MA':
                $uf = 'MA';
                break;
            case 'Minas Gerais':
            case 'MG':
                $uf = 'MG';
                break;
            case 'Mato Grosso do Sul':
            case 'MS':
                $uf = 'MS';
                break;
            case 'Mato Grosso':
            case 'MT':
                $uf = 'MT';
                break;
            case 'Pará':
            case 'Para':
            case 'PA':
                $uf = 'PA';
                break;
            case 'Paraíba':
            case 'Paraiba':
            case 'PB':
                $uf = 'PB';
                break;
            case 'Pernambuco':
                $uf = 'PE';
                break;
            case 'Piauí':
            case 'Piaui':
            case 'PI':
                $uf = 'PI';
                break;
            case 'Paraná':
            case 'Parana':
            case 'PR':
                $uf = 'PR';
                break;
            case 'Rio de Janeiro':
            case 'RJ':
                $uf = 'RJ';
                break;
            case 'Rio Grande do Norte':
            case 'RN':
                $uf = 'RN';
                break;
            case 'Rio Grande do Sul':
            case 'RS':
                $uf = 'RS';
                break;
            case 'Roraima':
            case 'RR':
                $uf = 'RR';
                break;
            case 'Rondônia':
            case 'Rondonia':
            case 'RO':
                $uf = 'RO';
                break;
            case 'Santa Catarina':
            case 'SC':
                $uf = 'SC';
                break;
            case 'Sergipe':
            case 'SE':
                $uf = 'SE';
                break;
            case 'São Paulo':
            case 'SP':
                $uf = 'SP';
                break;
            case 'Tocantins':
            case 'TO':
                $uf = 'TO';
                break;
            default:
                $uf = "";
                break;
        }

        return $uf;
    }

    function limpaTelefone($tel) {
        return preg_replace('([^0-9])','',$tel);
    }
    
    function isCpfValid($cpf) {
        //Etapa 1: Cria um array com apenas os digitos numéricos, isso permite receber o cpf em diferentes formatos como "000.000.000-00", "00000000000", "000 000 000 00" etc...
        $j=0;
        for($i=0; $i < (strlen($cpf)); $i++) {
            if(is_numeric($cpf[$i])) {
                $num[$j]=$cpf[$i];
                $j++;
            }
        }
        
        //Etapa 2: Conta os dígitos, um cpf válido possui 11 dígitos numéricos.
        if(count($num) != 11) {
            $isCpfValid = false;
        } else { //Etapa 3: Combinações como 00000000000 e 22222222222 embora não sejam cpfs reais resultariam em cpfs válidos após o calculo dos dígitos verificares e por isso precisam ser filtradas nesta parte.
            for($i=0; $i<10; $i++) {
                if ($num[0]==$i && $num[1]==$i && $num[2]==$i && $num[3]==$i && $num[4]==$i && $num[5]==$i && $num[6]==$i && $num[7]==$i && $num[8]==$i) {
                    $isCpfValid = false;
                    break;
                }
            }
        }

        //Etapa 4: Calcula e compara o primeiro dígito verificador.
        if(! isset($isCpfValid)) {
            $j=10;
            for($i=0; $i<9; $i++) {
                $multiplica[$i] = $num[$i] * $j;
                $j--;
            }

            $soma = array_sum($multiplica);	
            $resto = $soma % 11;			

            if($resto < 2) {
                $dg = 0;
            } else {
                $dg = 11 - $resto;
            }
            
            if($dg != $num[9]) {
                $isCpfValid = false;
            }
        }

        //Etapa 5: Calcula e compara o segundo dígito verificador.
        if( ! isset($isCpfValid)) {
            $j=11;
            for($i=0; $i<10; $i++) {
                $multiplica[$i]=$num[$i]*$j;
                $j--;
            }
            
            $soma = array_sum($multiplica);
            $resto = $soma % 11;
                
            if($resto < 2) {
                $dg = 0;
            } else {
                $dg = 11 - $resto;
            }
            
            if($dg != $num[10]) {
                $isCpfValid = false;
            } else {
                $isCpfValid = true;
            }
        }

        return $isCpfValid;					
    }

    public function initialize($paymentAction, $stateObject)
    {
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus($state);
        $stateObject->setIsNotified(false);
        $order = $this->getInfoInstance()->getOrder();

        Mage::getModel('core/resource_transaction')
            ->addObject($order)
            ->save();

        $xml_gateway = $this->gerarXML($order);

		return $this->enviaGateway($order, $xml_gateway);
    }

    public function assignData($data) {
        if (! ($data instanceof Varien_Object)) {
    		$data = new Varien_Object($data);
    	}
    	$info = $this->getInfoInstance();
    
    	$info->setCheckCartaobandeira($data->getCheckCartaobandeira())
    	->setCheckNome($data->getCheckNome())
    	->setCheckCpf($data->getCheckCpf())
    	->setCheckNumerocartao($data->getCheckNumerocartao())
    	->setCheckExpiracaomes($data->getCheckExpiracaomes())
    	->setCheckExpiracaoano($data->getCheckExpiracaoano())
    	->setCheckCodseguranca($data->getCheckCodseguranca())
    	->setCheckParcelamento($data->getCheckParcelamento())
    	->setCheckTefbandeira($data->getCheckTefbandeira())
    	->setCheckFormapagamento($data->getCheckFormapagamento());
    
    	return $this;
    }
 	
    public function validaNumeroDoCartao($numeroCartao, $codseg, $cartaobandeira) {
        
        $isValid = true;
        switch($cartaobandeira){
            
            case "cartao_amex":
                $prefix = substr($numeroCartao, 0,1);                
                if($prefix != "3"){
                    $isValid = false;                             
                } else if(strlen($numeroCartao) != 15){
                    $isValid = false;                            
                } else if(strlen($codseg) != 4){                            
                    $isValid = false;
                }

                break;
                
            case "cartao_diners":
                $prefix = substr($numeroCartao, 0,1);
                if($prefix != "3"){
                    $isValid = false;                            
                }else if(strlen($numeroCartao) != 14){
                    $isValid = false;
                }else if(strlen($codseg) != 3){                           
                    $isValid = false;
                }

                break;
                
            case "cartao_master":               
                $prefix = substr($numeroCartao, 0,1);       
                if($prefix != "5"){
                    $isValid = false;                             
                }else  if(strlen($numeroCartao) != 16){
                    $isValid = false;                           
                }else if(strlen($codseg) != 3){
                    $isValid = false;
                }

                break;
                
            case "cartao_visa":
                $prefix = substr($numeroCartao, 0,1);                   
                if($prefix != "4"){
                    $isValid = false;                            
                }else if(strlen($numeroCartao) != 13 && strlen($numeroCartao) != 16){
                    $isValid = false;                               
                }else  if(strlen($codseg) != 3){                           
                    $isValid = false;  
                }

            break;
            
            case "cartao_elo":
                break;
            
            default:	
                
        }	

        return $isValid;
    }
    
    public function validaDataCartaoDeCredito($mes, $ano) {
        $anoAtual = date("y");        
        $mesAtual = date("m");
        
        $dataAtual = (int)($anoAtual . "" . $mesAtual);
        $dataInformada = (int)($ano . "" . $mes);
        $isValid = true;
        
        if($dataInformada < $dataAtual){
            $isValid = false;
        } 
        
        return $isValid;
    }
    
    public function validate() {
    	parent::validate();
    
    	$info = $this->getInfoInstance();
    
    	$cartaobandeira     = str_replace("cc_", "", $info->getCheckCartaobandeira());
        $nome               = $info->getCheckNome();
    	$cpf                = $info->getCheckCpf();
    	$numerocartao       = $info->getCheckNumerocartao();
    	$expiracaomes       = $info->getCheckExpiracaomes();
    	$expiracaoano       = $info->getCheckExpiracaoano();
    	$codseguranca       = $info->getCheckCodseguranca();
    	$parcelamento       = $info->getCheckParcelamento();
    	$tefbandeira        = $info->getCheckTefbandeira();
    	$formapagamento     = $info->getCheckFormapagamento();
        
    	#verifica se a forma de pagamento foi selecionada
    	if(empty($formapagamento)) {
    		$errorCode = 'invalid_data';
    		$errorMsg = $this->_getHelper()->__('Selecione uma forma de pagamento');
    
    		#gera uma exception caso nenhuma forma de pagamento seja selecionada
    		Mage::throwException($errorMsg);
    	}
    
    	if($formapagamento=="cartaodecredito") {
    		if(empty($cartaobandeira) || empty($nome) || empty($cpf) || empty($numerocartao) || empty($codseguranca)) {
                $errorCode = 'invalid_data';
                $errorMsg = $this->_getHelper()->__('Campos de preenchimento obrigatório');
    
                if(! $this->isCpfValid($cpf)) {
                    $errorCode = 'invalid_data';
                    $errorMsg = $this->_getHelper()->__('CPF inválido.');

                    #gera uma exception caso nenhuma forma de pagamento seja selecionada
                    Mage::throwException($errorMsg);
                }

                $validCartao = $this->validaNumeroDoCartao($numerocartao, $codseguranca, $cartaobandeira);
                
                if(! $validCartao) {
                    $errorCode = 'invalid_data';
                    $errorMsg = $this->_getHelper()->__('Cartão inválido. Revise os dados informados e tente novamente.');

                    #gera uma exception caso nenhuma forma de pagamento seja selecionada
                    Mage::throwException($errorMsg);
                }

                $validadataCartao = $this->validaDataCartaoDeCredito($expiracaomes, $expiracaoano);
                
                if(! $validadataCartao) {
                    $errorCode = 'invalid_data';
                    $errorMsg = $this->_getHelper()->__('Cartão vencido. Revise os dados de expiracao e envie novamente.');

                    #gera uma exception caso nenhuma forma de pagamento seja selecionada
                    Mage::throwException($errorMsg);
                }

                #gera uma exception caso os campos do cartão nao forem preenchidos
                Mage::throwException($errorMsg);
            }
    	}
    
    	if($formapagamento === "tef") {
            if(empty($tefbandeira)){
                $errorCode = 'invalid_data';
                $errorMsg = $this->_getHelper()->__('Escolha o banco pelo qual deseja realizar a tranferẽncia eletrônica (TEF)');
    		
                #gera uma exception caso os campos de tef não forem preenchidos
                Mage::throwException($errorMsg);
    		}
    	}

    	return $this;
    }    
	
	public function gerarXML($order) {
		$xml = "";
        $incrementId = $order->getIncrementId();
                
		$customer = Mage::getSingleton('customer/session')->getCustomer();
        $billingId = $order->getBillingAddress()->getId();
        $customerAddressId = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling();
        
        if ($customerAddressId) {
           $address = Mage::getModel('customer/address')->load($customerAddressId);
        } else {           
           $address = Mage::getModel('sales/order_address')->load($billingId);              
        }
		
        if (str_replace(' ', '', $order->customer_firstname) !== "") {
            $customer_nome = $order->customer_firstname . " ".$order->customer_lastname;

        } else if (str_replace(' ', '', $customer->getName()) !== "") {
    		$customer_nome = $customer->getName();

        } else {
            $customer_nome = $_POST['billing']['firstname'] . " " . $_POST['billing']['lastname'];
        }
   	
    	$customer_email = $order->customer_email;
    	if ($customer_email=="") {
    		$customer_email = $customer->getEmail();
    	}
    	
        $storeId = Mage::app()->getStore()->getId();

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
		<carrinho>			
			<recebedor>
			<api_key>'.$this->getConfigData('api_key', $storeId).'</api_key>
			<email>'.$this->getConfigData('email_gateway', $storeId).'</email>
			</recebedor>';
			
			$consumer_tel = $address->getData("telephone");
			$consumer_tel = preg_replace('([^0-9])', '', $consumer_tel);
            $isValidTelephone = $this->isTelephoneValid($consumer_tel);
                        
            $xml.='
			<pagador>
				<nome>'.$customer_nome.'</nome>
				<email>'.$customer_email.'</email>';

            $logradouro = $address->getData("street");
            $numero = "0";
            $complemento = "Não Informado";
            $bairro = "Vide Logradouro";
            
            $mg_cidade = $address->getData("city");
            $mg_estado = $this->stringToUf($address->getData("region"));
            $mg_cep = $address->getData("postcode");

            $cidade = empty($mg_cidade) ? "Não Informado" : $mg_cidade;
            $estado = empty($mg_estado) ? "SP" : $mg_estado;
            $cep    = empty($mg_cep) ? "12345678" : $mg_cep;
		
			$xml .= '<enderecos>
				<endereco>
					<tipo>entrega</tipo>
						<logradouro>'.$logradouro.'</logradouro>
						<numero>'.$numero.'</numero>
						<complemento>'.$complemento.'</complemento>
						<bairro>'.$bairro.'</bairro>
						<cidade>'.$cidade.'</cidade>
						<estado>'.$estado.'</estado>
						<pais>BRA</pais>
						<cep>'.$cep.'</cep>
				   </endereco>
				</enderecos>';
				
			$xml.='
				<telefones>
					<telefone>
						<tipo>residencial</tipo>
						<numero>'.$consumer_tel.'</numero>
					</telefone>
				</telefones>
			</pagador>';
			
			$items = $order->getAllVisibleItems();
			$xml .= '
			<!-- Produtos -->
			<produtos>';
                        
            $totalItens= sizeof($items);                    

            $valorTotal = '';
            $freteTotal = '';
            $nome = '';
            $quantidadeTotal = '';
            $pesoTotal = '';
            $desc = '';
            $codigo = '';

            foreach ($items as $itemId => $item) {
                $valorTotal      += number_format($item->getPrice()*$item->getQtyToInvoice(),2,'.','');
                $freteTotal      += round( ($order->base_shipping_incl_tax/$order->total_item_count/$item->getQtyToInvoice()), 2);
                $quantidadeTotal += $item->getQtyToInvoice();
                $pesoTotal       += $item->getWeight();
                $cod              = str_replace("-","",$item->getSku());

                $preco_item = number_format($item->getPrice(), 2, '', '');
                $peso_item = number_format($item->getWeight(), 2, '', '');

                $xml .='<produto>
                            <codigo>'.$cod.'</codigo>
                            <descricao><![CDATA['.$item->getName().']]></descricao>
                            <quantidade>'.$item->getQtyToInvoice().'</quantidade>
                            <preco>'.$preco_item.'</preco>
                            <peso>'.$peso_item.'</peso>
                            <frete>0</frete>
                            <desconto>0</desconto>
                        </produto>';
            }

            $descontoTotal = abs(number_format($order->discount_amount,'2','.',''));

            $_totalData =$order->getData();
            $_grand = number_format($_totalData['grand_total'],2,'.', '');

            if(empty($_grand)) {
                 $_grand = number_format($valorTotal-$descontoTotal, 2, '.', '');
            }
                       
			$xml.='</produtos>';
			
			$info = $this->getInfoInstance();  
			$formapagamento=$info->getCheckFormapagamento();
			$cartaobandeira = $info->getCheckCartaobandeira();
			$nome=$info->getCheckNome();
			$cpf=$info->getCheckCpf();
			$numerocartao=$info->getCheckNumerocartao();
			$expiracaomes=$info->getCheckExpiracaomes();
			$expiracaoano=$info->getCheckExpiracaoano();
			$codseguranca=$info->getCheckCodseguranca();
			$parcelamento=$info->getCheckParcelamento();
			$tefbandeira=$info->getCheckTefbandeira();
			
			$xml_forma_pagamento="";
            
			if($formapagamento=="cartaodecredito") {
				$xml_forma_pagamento='
					<meio_de_pagamento>'.trim(str_replace("cc_", " ", $cartaobandeira)).'</meio_de_pagamento>
					<numero>'.$numerocartao.'</numero>
					<expiracao>'.$expiracaomes.'/'.$expiracaoano.'</expiracao>
					<codigo_de_seguranca>'.$codseguranca.'</codigo_de_seguranca>
					<parcelas>'.$parcelamento.'</parcelas>
					<portador>
						<nome>'.$nome.'</nome>
						<cpf>'.$cpf.'</cpf>
						<telefone>'.$this->limpaTelefone($address->getData("telephone")).'</telefone>
					</portador>';
			}
			
			if($formapagamento == "tef") {
				$xml_forma_pagamento = '<meio_de_pagamento>'.$tefbandeira.'</meio_de_pagamento>';
			}

			if($formapagamento == "boleto") {
				$xml_forma_pagamento = '<meio_de_pagamento>'.$formapagamento.'</meio_de_pagamento>';
			}
			
			$transacao_freteTotal = number_format($order->base_shipping_incl_tax, 2, '.', '');
            $transacao_descontoTotal = number_format($descontoTotal, 2, '.', '');
            $transacao_pesoTotal = number_format($pesoTotal, 2, '.', '');

            $fingerprint_akatus = isset($_POST['fingerprint_akatus']) ? $_POST['fingerprint_akatus'] : '';
            $fingerprint_partner_id = isset($_POST['fingerprint_partner_id']) ? $_POST['fingerprint_partner_id'] : '';

            $ipv4_address = filter_var($order->getRemoteIp(), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);

            $xml.='
                <!-- Transacao -->
                <transacao>
                    '.$xml_forma_pagamento.'
                    <!-- Dados do checkout -->
                    <moeda>BRL</moeda>
                    <frete>'.$transacao_freteTotal.'</frete> 
                    <desconto>'.$transacao_descontoTotal.'</desconto>
                    <peso>'.$transacao_pesoTotal.'</peso> 
                    <referencia>'.$incrementId.'</referencia>				
                    <fingerprint_akatus>'.$fingerprint_akatus.'</fingerprint_akatus>				
                    <fingerprint_partner_id>'.$fingerprint_partner_id.'</fingerprint_partner_id>				
                    <ip>'. $ipv4_address .'</ip>
                </transacao>';
                        
                        
		$xml.='</carrinho>';

		return $xml;
	}

	public function enviaGateway($order, $xml) {
		$orderId = $order->getId();
		
		$url = Akatus_Akatus_Helper_Data::getCarrinhoUrl();

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL,$url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0");
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$ret = curl_exec($curl);
		curl_close($curl);
                
		$data = Akatus_Akatus_Helper_Data::xml2array($ret);
		
		if($this->getConfigData('module_debug')=='1'){
            Mage::throwException("XML RECEBIDO:\n\n".$ret."\n\n\nXML Enviado:\n".$xml);
		}
                
        Mage::Log("..:: ENVIADO ::..\n\n".$this->filter($xml)."\n\n ..:: RECEBIDO ::..\n\n".$ret);

		$info = $this->getInfoInstance();
		$formapagamento = $info->getCheckFormapagamento();
        $resposta = $data["resposta"]["status"]["value"];
		 
		if($resposta == "erro") {

            $stateAndStatus = Mage_Sales_Model_Order::STATE_CANCELED;
            $order->setState($stateAndStatus, $stateAndStatus);
            $order->setStatus($stateAndStatus);
            $order->save();

            Mage::Log('Um erro ocorreu ao efetuar transação: '.$data["resposta"]["descricao"]["value"]);
			Mage::throwException("Não foi possível realizar a transação.");
            
		} else {
			if($resposta == "Em Análise"){

                try {
                    $this->protectCardNumber($info);
                    $transacaoId = $data["resposta"]["transacao"]["value"];
                    $this->SalvaIdTransacao($orderId,$transacaoId);

                    $stateAndStatus = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
                    $order->setState($stateAndStatus, $stateAndStatus);
                    $order->setStatus($stateAndStatus);
                    $order->save();
                    
                    $msg = "Seu pedido foi realizado com sucesso. Estamos aguardando a confirmação de sua administradora e assim que o pagamento for liberado enviaremos o produto.";
                    Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('checkout')->__($msg));
                    
				} catch (Exception $e){
                    Mage::Log($e->getMessage());
				}

			} else if ($resposta == "Aguardando Pagamento" || $resposta == "Processando"){

                $url_base = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
				
				if ($formapagamento == "boleto") {

					$url_destino = Akatus_Akatus_Helper_Data::getBoletoUrl();
					$str = $data['resposta']['transacao']['value'];
					$url_destino .= base64_encode($str).'.html';
					
					$info->setCheckBoletourl($url_destino);
                    $info->save();

					$transacaoId = $data["resposta"]["transacao"]["value"];
					$this->SalvaIdTransacao($orderId, $transacaoId);
					
					$msg='Transação realizada com sucesso. Clique no botão abaixo para imprimir seu boleto.<br/>';
                    $msg.="<a href='".$url_destino."' target='_blank'><img src='" . $url_base ."skin/frontend/default/default/images/boleto.gif' /></a>";
					
					Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('checkout')->__($msg));
				}
				
				if ($formapagamento=="tef") {

					$url_destino = Akatus_Akatus_Helper_Data::getTefUrl();
					$str = $data['resposta']['transacao']['value'];
					$url_destino .= base64_encode($str).'.html';
					
					$transacaoId = $data["resposta"]["transacao"]["value"];
					$this->SalvaIdTransacao($orderId, $transacaoId);
					
					$msg='Transação realizada com sucesso. Clique no botão abaixo e você será redirecionado para seu banco.<br/>';
                    $msg.="<a href='".$url_destino."' target='_blank'><img src='" . $url_base ."/skin/frontend/default/default/images/tef.gif' /></a>";
					
					Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('checkout')->__($msg));
				}	
                
			} else {

                $stateAndStatus = Mage_Sales_Model_Order::STATE_CANCELED;
                $order->setState($stateAndStatus, $stateAndStatus, 'Pagamento não autorizado pela operadora de cartão de crédito');
                $order->setStatus($stateAndStatus);
                Mage::getModel('core/resource_transaction')
                    ->addObject($info)
                    ->addObject($order)
                    ->save();

				Mage::Log('Pagamento não autorizado. ID do pedido: ' . $order->getId());
                Mage::throwException("Pagamento não autorizado.\nConsulte a operadora do seu cartão de crédito para maiores informações.");
			}
		}
	}
	
	public function SalvaIdTransacao($orderId, $transacaoId) {
		//Salva as informaces do pedido para Validacao com o NIP
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');	
		$db->query("DELETE FROM akatus_transacoes WHERE idpedido='".$orderId."'");
		$db->query("INSERT into akatus_transacoes (idpedido,codtransacao) VALUES('".$orderId."','".$transacaoId."')");
    }

    private function protectCardNumber($info)
    {
        $numeroCartao = $info->getCheckNumerocartao();
        $cardDigits = '';

        $first6 = substr($numeroCartao, 0, 6);
        $last4 = substr($numeroCartao,(strlen($numeroCartao)-4),strlen($numeroCartao));
                
        $cardDigits = $first6 . "******" . $last4;	
            
        $info->setCheckNumerocartao($cardDigits);
    }

    private function filter($string)
    {                                                                                                                                                                                     
        $patterns = array(
            '/<numero>.*<\/numero>/',
            '/<codigo_de_seguranca>.*<\/codigo_de_seguranca>/',
            '/<expiracao>.*<\/expiracao>/'
        );

        $replacements = array(
            '<numero>INFORMACAO_FILTRADA_POR_SEGURANCA</numero>',
            '<codigo_de_seguranca>INFORMACAO_FILTRADA_POR_SEGURANCA</codigo_de_seguranca>',
            '<expiracao>INFORMACAO_FILTRADA_POR_SEGURANCA</expiracao>'
        );

        return preg_replace($patterns, $replacements, $string);

    }   

}
