<?php

class Akatus_Akatus_RefundController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $orderId = $this->getRequest()->getParam('order', false);
        
        if ($orderId) {
            
            $url = Akatus_Akatus_Helper_Data::getEstornoUrl();
            $order = Mage::getModel('sales/order')->load($orderId);
            $xml = $this->getXML($order);

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL,$url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0");
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
            curl_close($curl);

            $responseArray = Akatus_Akatus_Helper_Data::xml2array($response);

            if ($responseArray['resposta']['codigo-retorno']['value'] == '0') {
                Mage::getSingleton('adminhtml/session')->addSuccess('Transação estornada com sucesso. Aguarde o NIP para a atualização do pedido na sua loja.');
            } else {
                Mage::getSingleton('adminhtml/session')->addError('Não foi possível estornar a transação: ' . utf8_decode($responseArray['resposta']['mensagem']['value']));
            }
            
            session_write_close();
            $this->_redirect('adminhtml/sales_order/view/', array('order_id' => $orderId));
        
        }
    }
    
    private function getXML($order)
    {
        $db = Mage::getSingleton('core/resource')->getConnection('core_write');

        $resultset = $db->query("SELECT codtransacao FROM akatus_transacoes WHERE idpedido = '" . $order->getId() . "'");
        $result = $resultset->fetch();
        $transaction = $result['codtransacao'];
        
        $apiKey = Mage::getStoreConfig('payment/akatus/api_key', $order->getStoreId());
        $email = Mage::getStoreConfig('payment/akatus/email_gateway', $order->getStoreId());

        return "<estorno><transacao>" . $transaction . "</transacao><api_key>" . $apiKey . "</api_key><email>" . $email . "</email></estorno>";
    }
}
