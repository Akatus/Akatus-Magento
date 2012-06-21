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
				$formaPagamento = "Boleto Banc�rio";
				break;
			case "cartaodecredito":
				$formaPagamento = "Cart�o de Cr�dito";
				break;
			case "tef":
				$formaPagamento = "Cart�o de D�bito - TEF";
				break;
		}
		/*$array = array(
			Mage::helper('payment')->__('Forma de Pagamento') => $formaPagamento
		);*/
		
		#var_dump($info);
		
		if($info->getCheckFormapagamento() == 'boleto'){
			/*$array = array(
				#Mage::helper('payment')->__('Forma de Pagamento') => $info->getCheckFormapagamento()
				Mage::helper('payment')->__('Forma de Pagamento') => utf8_encode("Boleto Banc�rio"),
				Mage::helper('payment')->__('Segunda Via') => $info->getCheckBoletourl()
			);*/
			
			echo utf8_encode("<table>
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
		#	echo utf8_encode("<a href = '{$info->getCheckBoletourl()}'>Imprimir 2� via</a>");
			
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
			switch($info->getCheckCartaobandeira()){
				case "cartao_amex":
					$cartao = "Cart�o American Express";
					break;
				case "cartao_elo":
					$cartao = "Cart�o Elo";
					break;
				case "cartao_master":
					$cartao = "Cart�o Master";
					break;
				case "cartao_diners":
					$cartao = "Cart�o Diners";
					break;
				case "cartao_visa":
					$cartao = "Cart�o Visa";
					break;					
			}
			$array = array(
				utf8_encode(Mage::helper('payment')->__('Bandeira do Cart�o')) => utf8_encode($cartao),
				Mage::helper('payment')->__('Nome') => $info->getCheckNome(),
				Mage::helper('payment')->__('Cpf') => $info->getCheckCpf(),
				utf8_encode(Mage::helper('payment')->__('Numero do Cart�o')) => $numCart,
				#Mage::helper('payment')->__('Expiracao M�s') => $info->getCheckExpiracaomes(),
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
		$transport->addData($array
		);
		return $transport;
	}
}
