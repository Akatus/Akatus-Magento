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
     * @see todas as floags disponiveis in Mage_Payment_Model_Method_Abstract
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
    protected $_canSaveCc = false;
  
    function isTelephoneValid($tel){
        
        $valid = true;
        
              
        $size = strlen($tel);
        
        if($size == 10 || $size == 11){
            
        } else {
            $valid = false;
        }
        
        
        if(!$valid){
            $errorMsg = $this->_getHelper()->__('Telefone inválido. Deve ser informado o código de área com 2 dígitos seguido do número do telefone com 8 ou 9 dígitos, e somente números (Ex.: 1199999999).');

                #gera uma exception caso nenhuma forma de pagamento seja selecionada
                Mage::throwException($errorMsg);
        }
        
        
        
    }
    
    
     function isCpfValid($cpf){
			//Etapa 1: Cria um array com apenas os digitos numéricos, isso permite receber o cpf em diferentes formatos como "000.000.000-00", "00000000000", "000 000 000 00" etc...
			$j=0;
			for($i=0; $i<(strlen($cpf)); $i++)
				{
					if(is_numeric($cpf[$i]))
						{
							$num[$j]=$cpf[$i];
							$j++;
						}
				}
			//Etapa 2: Conta os dígitos, um cpf válido possui 11 dígitos numéricos.
			if(count($num)!=11)
				{
					$isCpfValid=false;
				}
			//Etapa 3: Combinações como 00000000000 e 22222222222 embora não sejam cpfs reais resultariam em cpfs válidos após o calculo dos dígitos verificares e por isso precisam ser filtradas nesta parte.
			else
				{
					for($i=0; $i<10; $i++)
						{
							if ($num[0]==$i && $num[1]==$i && $num[2]==$i && $num[3]==$i && $num[4]==$i && $num[5]==$i && $num[6]==$i && $num[7]==$i && $num[8]==$i)
								{
									$isCpfValid=false;
									break;
								}
						}
				}
			//Etapa 4: Calcula e compara o primeiro dígito verificador.
			if(!isset($isCpfValid))
				{
					$j=10;
					for($i=0; $i<9; $i++)
						{
							$multiplica[$i]=$num[$i]*$j;
							$j--;
						}
					$soma = array_sum($multiplica);	
					$resto = $soma%11;			
					if($resto<2)
						{
							$dg=0;
						}
					else
						{
							$dg=11-$resto;
						}
					if($dg!=$num[9])
						{
							$isCpfValid=false;
						}
				}
			//Etapa 5: Calcula e compara o segundo dígito verificador.
			if(!isset($isCpfValid))
				{
					$j=11;
					for($i=0; $i<10; $i++)
						{
							$multiplica[$i]=$num[$i]*$j;
							$j--;
						}
					$soma = array_sum($multiplica);
					$resto = $soma%11;
					if($resto<2)
						{
							$dg=0;
						}
					else
						{
							$dg=11-$resto;
						}
					if($dg!=$num[10])
						{
							$isCpfValid=false;
						}
					else
						{
							$isCpfValid=true;
						}
				}
			//Trecho usado para depurar erros.
			/*
			if($isCpfValid==true)
				{
					echo "<font color=\"GREEN\">Cpf é Válido</font>";
				}
			if($isCpfValid==false)
				{
					echo "<font color=\"RED\">Cpf Inválido</font>";
				}
			*/
			//Etapa 6: Retorna o Resultado em um valor booleano.
			return $isCpfValid;					
		}
    
    public function assignData($data)
    {
    	if (!($data instanceof Varien_Object)) {
    		$data = new Varien_Object($data);
    	}
    	$info = $this->getInfoInstance();
    
		$checkNomeCC = $data->getCheckFormapagamento();	
		$checkBandCC = $data->getCheckCartaobandeira();	
			/*
		if($checkNomeCC == "cartaodecredito"):

			if($checkBandCC == "cartao_amex"){
				
				$numeroCartao = $data->getCheckNumerocartao();
				$last5 = substr($numeroCartao,(strlen($numeroCartao)-5),strlen($numeroCartao));
				
				$numCart = "XXXX.XXXXXX." . $last5;
				
			}else{
			
				$numeroCartao = $data->getCheckNumerocartao();
				$last4 = substr($numeroCartao,(strlen($numeroCartao)-4),strlen($numeroCartao));
				
				$numCart = "XXXX.XXXX.XXXX." . $last4;
				
			}
			
		endif;
		*/
	
    	$info->setCheckCartaobandeira($data->getCheckCartaobandeira())
    	->setCheckNome($data->getCheckNome())
    	->setCheckCpf($data->getCheckCpf())
    	#->setCheckNumerocartao($numCart)
    	->setCheckNumerocartao($data->getCheckNumerocartao())
    	->setCheckExpiracaomes($data->getCheckExpiracaomes())
    	->setCheckExpiracaoano($data->getCheckExpiracaoano())
    	->setCheckCodseguranca($data->getCheckCodseguranca())
    	->setCheckParcelamento($data->getCheckParcelamento())
    	->setCheckTefbandeira($data->getCheckTefbandeira())
    	->setCheckFormapagamento($data->getCheckFormapagamento());
    
    	return $this;
    }
 	
    public function validaNumeroDoCartao($numeroCartao, $codseg, $cartaobandeira){
        
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
    
    
     public function validaDataCartaoDeCredito($mes, $ano){
        
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
    
    public function validate()
    {
    	parent::validate();
    
    	$info = $this->getInfoInstance();
    
    	$cartaobandeira = str_replace("cc_", "", $info->getCheckCartaobandeira());
        $nome=$info->getCheckNome();
    	$cpf=$info->getCheckCpf();
    	$numerocartao=$info->getCheckNumerocartao();
    	$expiracaomes=$info->getCheckExpiracaomes();
    	$expiracaoano=$info->getCheckExpiracaoano();
    	$codseguranca=$info->getCheckCodseguranca();
    	$parcelamento=$info->getCheckParcelamento();
    	$tefbandeira=$info->getCheckTefbandeira();
    	$formapagamento=$info->getCheckFormapagamento();
    
        
    	#verifica se a forma de pagamento foi selecionada
    	if(empty($formapagamento)){
    		$errorCode = 'invalid_data';
    		$errorMsg = $this->_getHelper()->__('Selecione uma forma de pagamento');
    
    		#gera uma exception caso nenhuma forma de pagamento seja selecionada
    		Mage::throwException($errorMsg);
    	}
    
    	if($formapagamento=="cartaodecredito"){
    		if(empty($cartaobandeira) || empty($nome) || empty($cpf) || empty($numerocartao) || empty($codseguranca)){
    		$errorCode = 'invalid_data';
    		$errorMsg = $this->_getHelper()->__('Campos de preenchimento obrigatório');
    
                $validCPF = $this->isCpfValid($cpf);
        
                
                if(!$validCPF) {

                    $errorCode = 'invalid_data';
                        $errorMsg = $this->_getHelper()->__('CPF inválido.');

                        #gera uma exception caso nenhuma forma de pagamento seja selecionada
                        Mage::throwException($errorMsg);

                }


            //age::throwException($cartaobandeira);
                $validCartao = $this->validaNumeroDoCartao($numerocartao, $codseguranca, $cartaobandeira);
                if(!$validCartao){
                    $errorCode = 'invalid_data';
                    $errorMsg = $this->_getHelper()->__('Cartão inválido. Revise os dados informados e tente novamente.');

                    #gera uma exception caso nenhuma forma de pagamento seja selecionada
                    Mage::throwException($errorMsg);
                }

                $validadataCartao = $this->validaDataCartaoDeCredito($expiracaomes, $expiracaoano);
                if(!$validadataCartao){
                    $errorCode = 'invalid_data';
                    $errorMsg = $this->_getHelper()->__('Cartão vencido. Revise os dados de expiracao e envie novamente.');

                    #gera uma exception caso nenhuma forma de pagamento seja selecionada
                    Mage::throwException($errorMsg);
                }

                
                
                
                
    		#gera uma exception caso os campos do cartão nao forem preenchidos
    		Mage::throwException($errorMsg);
    		}
    	}
    
    	if($formapagamento=="tef"){
    		if(empty($tefbandeira)){
    		$errorCode = 'invalid_data';
    		$errorMsg = $this->_getHelper()->__('Campo de preenchimento obrigatório');
    
    		
    		#gera uma exception caso os campos de tef não forem preenchidos
    		Mage::throwException($errorMsg);
    		}
    	}
    	    	
    	return $this;
    }    
 
	
	public function authorize(Varien_Object $payment, $amount)
	{
		#monta XML para enviar ao gateway
		$xml_gateway=$this->GeraXMl($payment, $amount);

		#envia ao gateway as informacoes de pagamento
		$status=$this->EnviaGateway($payment, $xml_gateway);
		
		return $this;
	}
    
	
	
	public function GeraXML(Varien_Object $payment, $amount){
		/*
		 * Constroi o XML que será enviado ao gateway
		 */
		
		#variavel que vai conter o XML para ser enviado ao servidor
		$xml="";
		
		#id do pedido
		$orderId = $payment->getParentId();
                
                
		$order = Mage::getModel('sales/order')->load($orderId);
	
		#regata asa informacoes do cliente para montar o XMl
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		$shippingId = $order->getShippingAddress()->getId();
    	$address = Mage::getModel('sales/order_address')->load($shippingId);
    	
		
    	$customer_nome=$order->customer_firstname . " ".$order->customer_lastname;
    	if($customer_nome==""){
    		$customer_nome=$customer->getName();
    	}
    	
    	$customer_email=$order->customer_email;
    	if($customer_email==""){
    		$customer_email=$customer->getEmail();
    	}
        
        
    	
    	
		#configura o xml
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
		<carrinho>
			<!-- LOJISTA-->
			<recebedor>
			<api_key>'.$this->getConfigData('api_key').'</api_key>
			<email>'.$this->getConfigData('email_gateway').'</email>
			</recebedor>';
			
			$consumer_tel=$address->getData("telephone");
			$consumer_tel= preg_replace("[^0-9]", "", $consumer_tel);
            $isValidTelephone = $this->isTelephoneValid($consumer_tel);
                        
            $xml.='
			<!-- CLIENTE -->
			<pagador>
				<nome>'.$customer_nome.'</nome>
				<email>'.$customer_email.'</email>
				';
				/*
				 * Bloco de endereço comentado conforme Solicitação.
				<!-- ENDERECO OPCIONAL -->
				<enderecos>
				<endereco>
					<tipo>entrega</tipo>
						<logradouro>'.$address->getData("street").'</logradouro>
						<numero></numero>
						<complemento></complemento>
						<bairro></bairro>
						<cidade>'.$address->getData("city").'</cidade>
						<estado>'.$address->getData("region").'</estado>
						<pais>'.$address->getData("country_id").'</pais>
						<cep>'.$address->getData("postcode").'</cep>
				   </endereco>
				</enderecos>
				*/
			$xml.='
				<telefones>
					<telefone>
						<tipo>residencial</tipo>
						<numero>'.$consumer_tel.'</numero>
					</telefone>
				</telefones>
			</pagador>
			';
			
    	
    		
    		#pega todos os itens do pedido para enviar ao gateway
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
                        
			foreach ($items as $itemId => $item)
			{
				//$preco= number_format($item->getPrice(),'2','','');

                            
                                $preco = $item->getPrice();
				
                                
                        $valorTotal      += number_format($item->getPrice()*$item->getQtyToInvoice(),2,'.','');
                        $freteTotal      += round(($order->base_shipping_incl_tax/$order->total_item_count)/$item->getQtyToInvoice(), 2);
                        $quantidadeTotal += $item->getQtyToInvoice();
                        $pesoTotal       += d($item->getWeight());
                        $descricao        = $item->getName();
                        $cod              = str_replace("-","",$item->getSku());
                                
                                Mage::Log('SKU:'.$item->getSku());
                                Mage::Log('descricao(item->getName()):'.$item->getName());
                                Mage::Log('quantidade (item->getQtyToInvoice()):'.$item->getQtyToInvoice());
                                Mage::Log('preco (item->getPrice()):'.$item->getPrice());
                                Mage::Log('peso (item->getWeight()):'.$item->getWeight());
                                Mage::Log('frete:'.number_format((($order->base_shipping_incl_tax/$order->total_item_count)/$item->getQtyToInvoice()),'2','',''));
                                Mage::Log('desconto (item->discount_amount):'.$item->discount_amount);
                                Mage::Log('desconto (item->discount_percent):'.$item->discount_percent);
                                Mage::Log('desconto carrinho (order->discount_amount):'.$order->discount_amount);
                                Mage::Log('grand_amount:');
                        }
                        
                        $descontoTotal = abs(number_format($order->discount_amount,'2','.',''));
                        
                        $_totalData =$order->getData();
                        $_grand = number_format($_totalData['grand_total'],2,'.');
                        Mage::Log('grand_amount:'.$_grand);
                        Mage::Log('precototal:'.$valorTotal);
                                             
                        $xml .='
				<produto>
					<codigo>'.$cod.'</codigo>
					<descricao>'.$descricao.'</descricao>
					<quantidade>1</quantidade>
					<preco>'.$_grand.'</preco>
					<peso>'.$pesoTotal.'</peso>
					<frete>'.$freteTotal.'</frete>
					<desconto>'.$descontoTotal.'</desconto>
				</produto>';
                        
			$xml.='</produtos>';
			
			
			#forma de pagamento
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
			if($formapagamento=="cartaodecredito"){
				$xml_forma_pagamento='
					<meio_de_pagamento>'.trim(str_replace("cc_", " ", $cartaobandeira)).'</meio_de_pagamento>
					<numero>'.$numerocartao.'</numero>
					<expiracao>'.$expiracaomes.'/'.$expiracaoano.'</expiracao>
					<codigo_de_seguranca>'.$codseguranca.'</codigo_de_seguranca>
					<parcelas>'.$parcelamento.'</parcelas>
					<portador>
						<nome>'.$nome.'</nome>
						<cpf>'.$cpf.'</cpf>
						<telefone>'.$address->getData("telephone").'</telefone>
					</portador>';
			}
			
			if($formapagamento=="tef"){
				$xml_forma_pagamento='
					<meio_de_pagamento>'.$tefbandeira.'</meio_de_pagamento>';
			}

			if($formapagamento=="boleto"){
				$xml_forma_pagamento='
					<meio_de_pagamento>'.$formapagamento.'</meio_de_pagamento>';
			}
			
			
			
                        
                        $xml.='
			<!-- Transacao -->
			<transacao>
				'.$xml_forma_pagamento.'
				<!-- Dados do checkout -->
				<moeda>BRL</moeda>
				<frete_total>'.$freteTotal.'</frete_total> 
				<desconto_total>000</desconto_total>
				<peso_total>'.$pesoTotal.'</peso_total> 
				<referencia>'.$orderId.'</referencia>				
			</transacao>';
                        
                        
                        
		$xml.='</carrinho>';
                //discount_amount
                Mage::Log("XML pronto para o envio. Vamos ver o que dá....");
                
		return $xml;
	}

	public function EnviaGateway(Varien_Object $payment,$xml)
	{
		/*
		 * Faz o envio do XML para o gateway de pagamento.
		 */
		$status="";
		$orderId = $payment->getId();
		
		$url = 'https://www.akatus.com/api/v1/carrinho.xml';
		#$url = 'https://dev.akatus.com/api/v1/carrinho.xml';
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL,$url);
		#curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		# curl_setopt($curl, CURLOPT_USERPWD, $user . ":" . $passwd);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0");
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$ret = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
                
		$data = $this->xml2array($ret);
		
		
		
		#Criado para facilitar o ambiente de depuração.
		if($this->getConfigData('module_debug')=='1'){
		Mage::throwException("XML RECEBIDO:\n\n".$ret."\n\n\nXML Enviado:\n".$xml);
		}
                
                Mage::Log("..:: ENVIADO ::..\n\n".$xml."\n\n ..:: RECEBIDO ::..\n\n".$ret);
		/*
		print_r($ret);
		print_r($data);	
		*/
		//echo "<br />Status:".$data["resposta"]["status"]["value"];			
		

		$info = $this->getInfoInstance();
		$formapagamento=$info->getCheckFormapagamento();
		 
		//print_r($formadepagamento);
		//exit();			

		if($data["resposta"]["status"]["value"]=="erro")
		{
			#exibe a mensagem de erro
                        Mage::Log('Deu erro meu velho: '.$data["resposta"]["descricao"]["value"]);
			Mage::throwException("Não foi possível realizar sua transação");
			
			#exibe a mensagem de erro
			#Mage::getSingleton('checkout/session')->addError(Mage::helper('checkout')->__("Não foi possivel realizar sua transação")); 
		}
		else
		{
			//echo "Não deu erro, retornou algum outro status";
			if($data["resposta"]["status"]["value"]=="Em Análise"){
				//echo "Em análise";
				//exit;			
				//Salva no sistema o ID da transação
                                Mage::Log('Veio Em Análise, passou o cartão, é setar o status e partir pro abraço.');
				try{
				$transacaoId=$data["resposta"]["transacao"]["value"];
				$this->SalvaIdTransacao($orderId,$transacaoId);
				
				$order = Mage::getModel('sales/order')->load($orderId);
				$order->setStatus('pending_payment');
				$order->save();
				$info = $this->getInfoInstance();
				$formadepagamento = $info->getCheckFormapagamento();
				//Mage::throwException($formadepagamento);
				//Mage::throwException("Seu pedido foi realizado com sucesso. Estamos aguardando a confirmação de sua administradora e assim que o pagamento for liberado enviaremos o produto");
				$msg = "Seu pedido foi realizado com sucesso. Estamos aguardando a confirmação de sua administradora e assim que o pagamento for liberado enviaremos o produto";
				Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('checkout')->__($msg));
				} catch (Exception $e){
					$e->getMessage();
				
				}

			}elseif($data["resposta"]["status"]["value"]=="Aguardando Pagamento" || $data["resposta"]["status"]["value"]=="Processando"){
				
				$info = $this->getInfoInstance();
				$formapagamento=$info->getCheckFormapagamento();
				//ge::throwException($formadepagamento);
				//$url_destino='https://www.akatus.com/boleto/';
				
				if($formapagamento=="boleto"){
					
					#$url_destino='https://dev.akatus.com/boleto/';
					$url_destino='https://www.akatus.com/boleto/';
					$str = $data['resposta']['transacao']['value'];
					$url_destino.=base64_encode($str).'.html';
					
					$payment->setCheckBoletourl($url_destino);
					$payment->save();	
					
					$transacaoId=$data["resposta"]["transacao"]["value"];
					$this->SalvaIdTransacao($orderId,$transacaoId);
					
					#monta a mensagem
					#$msg="<img src = 
					$msg='Transação realizada com sucesso. Clique na url abaixo para imprimir seu boleto.<br/>';
					$msg.="<a href='".$url_destino."' target='_blank'>".$url_destino."</a>";
					
					Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('checkout')->__($msg));
				}
				
				if($formapagamento=="tef"){
					
					#$url_destino='https://dev.akatus.com/tef/';
					$url_destino='https://www.akatus.com/tef/';
					$str = $data['resposta']['transacao']['value'];
					$url_destino.=base64_encode($str).'.html';
					
					$transacaoId=$data["resposta"]["transacao"]["value"];
					$this->SalvaIdTransacao($orderId,$transacaoId);
					
					#monta a mensagem
					$msg='Transação realizada com sucesso. Clique na url abaixo e você será redirecionado para seu banco.<br/>';
					$msg.="<a href='".$url_destino."' target='_blank'>".$url_destino."</a>";
					
					Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('checkout')->__($msg));
				}	
			} else {
	//			$info = $this->getInfoInstance();
       //         $formapagamento=$info->getCheckFormapagamento();
                  Mage::throwException("Pagamento não autorizado. Consulte sua operadora para maiores informações.");
                  //$msq = "Pagamento não autorizado. Consulte sua operadora para maiores informações.";              
				//Mage::throwException("Cartão Recusado:".$data["resposta"]["status"]["value"]."<br />Forma de pagamento:".$formadepagamento);
			}
		}
		
		
	}
	
	public function SalvaIdTransacao($orderId, $transacaoId)
	{
		//Salva as informaces do pedido para Validacao com o NIP
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');	
		//$db->query("DELETE FROM akatus_transacoes WHERE idpedido='".$orderId."'");
		$db->query("INSERT into akatus_transacoes (idpedido,codtransacao) VALUES('".$orderId."','".$transacaoId."')");
                
	}
	
	
	
	function xml2array($contents, $get_attributes=1)
	{
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
?>
