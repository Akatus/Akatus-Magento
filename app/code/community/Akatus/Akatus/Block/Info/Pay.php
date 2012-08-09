<?php
class Akatus_Akatus_Block_Info_Pay extends Mage_Payment_Block_Info
{
	protected function _prepareSpecificInformation($transport = null)
	{
		if (null !== $this->_akatusSpecificInformation) {
			return $this->_akatusSpecificInformation;
		}
		$info = $this->getInfo();
		$transport = new Varien_Object();
		$transport = parent::_prepareSpecificInformation($transport);
		/*
		switch($info->getCheckFormapagamento()){
			case "boleto":
				$formaPagamento = "Boleto Bancário";
				break;
			case "cartaodecredito":
				$formaPagamento = "Cartão de Crédito";
				break;
			case "tef":
				$formaPagamento = "Cartão de Débito - TEF";
				break;
		}
		/*$array = array(
			Mage::helper('payment')->__('Forma de Pagamento') => $formaPagamento
		);*/

		#var_dump($info);
	
	if($info->getCheckFormapagamento() == 'boleto'){
			$array = array(
				Mage::helper('payment')->__('Forma de Pagamento') => $info->getCheckFormapagamento(),
				Mage::helper('payment')->__('Forma de Pagamento') => utf8_encode("Boleto Bancário"),
				Mage::helper('payment')->__('Segunda Via') => $info->getCheckBoletourl()
			);

			echo ("<table>
                                    <tbody>
                                        <tr>
                                            <th>
                                            <strong>Forma de Pagamento:</strong>
                                            </th>
                                        </tr>
                                        <tr>
                                            <td>Boleto Bancário</td>
                                        </tr>
                                        <tr>
                                            <th>
                                            <strong>Segunda Via</strong>
                                            </th>
                                        </tr>
                                        <tr>
                                            <td><a href = '{$info->getCheckBoletourl()}'>Imprimir</a></td>
                                        </tr>
                                    </tbody>
                                </table>");
		#	echo utf8_encode("<a href = '{$info->getCheckBoletourl()}'>Imprimir 2 via</a>");

		}elseif($info->getCheckFormapagamento() == 'cartaodecredito'){
			$checkBandCC = $info->getCheckCartaobandeira();
			if($checkBandCC == "cartao_amex"){

				$numeroCartao = $info->getCheckNumerocartao();
				$last5 = substr($numeroCartao,(strlen($numeroCartao)-5),strlen($numeroCartao));

				$numCart = "XXXX.XXXXXX." . $last5;

			}else{

				$numeroCartao = $info->getCheckNumerocartao();
				$last4 = substr($numeroCartao,(strlen($numeroCartao)-4),strlen($numeroCartao));

				$numCart = "XXXX.XXXX.XXXX." . $last4;

			}
			
			$cartaoLabel = str_replace("cc_", "", $info->getCheckCartaobandeira());
			switch($cartaoLabel){
				case "cartao_amex":
					$cartao = "Cartão American Express";
					break;
				case "cartao_elo":
					$cartao = "Cartão Elo";
					break;
				case "cartao_master":
					$cartao = "Cartão Master";
					break;
				case "cartao_diners":
					$cartao = "Cartão Diners";
					break;
				case "cartao_visa":
					$cartao = "Cartão Visa";
					break;					
			}
			$array = array(
				(Mage::helper('payment')->__('Bandeira do Cartão')) => ($cartao),
				Mage::helper('payment')->__('Nome') => $info->getCheckNome(),
				Mage::helper('payment')->__('Cpf') => $info->getCheckCpf(),
				(Mage::helper('payment')->__('Numero do Cartão')) => $numCart,
				#Mage::helper('payment')->__('Expiracao Mês') => $info->getCheckExpiracaomes(),
				#Mage::helper('payment')->__('Check Expiracaoano') => $info->getCheckExpiracaoano(),

				#Mage::helper('payment')->__('Check Codseguranca') => $info->getCheckCodseguranca()
			);
		}else{

			$array = array(

				Mage::helper('payment')->__('Bandeira') => $info->getCheckTefbandeira()

			);
		}

                      /*Mage::helper('payment')->__('Check Cartaobandeira') => $info->getCheckCartaobandeira(),
			Mage::helper('payment')->__('Check Nome') => $info->getCheckNome(),
			Mage::helper('payment')->__('Check Cpf') => $info->getCheckCpf(),
			Mage::helper('payment')->__('Check Numerocartao') => $info->getCheckNumerocartao(),
			Mage::helper('payment')->__('Check Expiracaomes') => $info->getCheckExpiracaomes(),
			Mage::helper('payment')->__('Check Expiracaoano') => $info->getCheckExpiracaoano(),
			Mage::helper('payment')->__('Check Codseguranca') => $info->getCheckCodseguranca(),
			Mage::helper('payment')->__('Check Tefbandeira') => $info->getCheckTefbandeira(),*/
		$transport->addData($array);
		return $transport;
	}
}