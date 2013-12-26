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

    	if ($info->getCheckFormapagamento() == 'boleto') {
			echo ("<table>
                        <tbody>
                            <tr>
                                <td>
                                    <strong>Forma de Pagamento: </strong>Boleto Bancário
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Segunda Via: </strong><a href = '{$info->getCheckBoletourl()}' target='_blank'>Imprimir</a><br>
                                </td>
                            </tr>
                        </tbody>
                    </table>");

		} elseif ($info->getCheckFormapagamento() == 'cartaodecredito') {

            switch($info->getCheckCartaobandeira()){

            case "cc_cartao_amex":
                $cartao = "American Express";
                break;
            case "cc_cartao_elo":
                $cartao = "Elo";
                break;
            case "cc_cartao_master":
                $cartao = "Mastercard";
                break;
            case "cc_cartao_diners":
                $cartao = "Diners Club";
                break;
            case "cc_cartao_visa":
                $cartao = "Visa";
                break;          
            } 

			echo ("<table>
                        <tbody>
                            <tr>
                                <td>
                                    <strong>Cartão: </strong>{$cartao}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Nome: </strong>{$info->getCheckNome()}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>CPF: </strong>{$info->getCheckCpf()}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Número do Cartão: </strong>{$info->getCheckNumerocartao()}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Número de Parcelas: </strong>{$info->getCheckParcelamento()}
                                </td>
                            </tr>
                        </tbody>
                    </table>");

		} elseif ($info->getCheckFormapagamento() == 'tef') {

            switch($info->getCheckTefbandeira()){

            case "tef_itau":
                $banco = "Itaú";
                break;
            case "tef_bradesco":
                $banco = "Bradesco";
                break;
            case "tef_bb":
                $banco = "Banco do Brasil";
                break;
            } 

			echo ("<table>
                        <tbody>
                            <tr>
                                <td>
                                    <strong>TEF: </strong>{$banco}
                                </td>
                            </tr>
                        </tbody>
                    </table>");
		}


        if ($this->isToShowRefund($info->getOrder())) {

            $estornoURL = $this->getEstornoURL($info->getOrder()->getId());

            echo ("<table>
                        <tbody>
                            <tr>
                                <td>
                                    <strong>Estorno:</strong>
                                </td>
                            </tr>
                            <tr>
                                <td><a href ='$estornoURL'>Solicitar estorno</a><br></td>
                            </tr>
                        </tbody>
                    </table>");
        }

		return $transport;
	}
   
    private function isToShowRefund($order) 
    {
        if (isset($order)) {
        
            $adminSession = Mage::getSingleton('admin/session', array('name' => 'adminhtml'));
            $isAdmin = $adminSession->isLoggedIn();
            $state = $order->getState();

            if ($isAdmin && ($state === Mage_Sales_Model_Order::STATE_COMPLETE || $state === Mage_Sales_Model_Order::STATE_PROCESSING)) {
                return true;
            }
        }

        return false;
    }

    private function getEstornoURL($orderId)
    {
        return Mage::helper("adminhtml")->getUrl("akatus/refund/index", array("order" => $orderId));
    }
}
