<?php

class StatusTransacaoAkatus
{
    const AGUARDANDO_PAGAMENTO  = 'Aguardando Pagamento';
    const EM_ANALISE            = 'Em Análise';
    const APROVADO              = 'Aprovado';
    const CANCELADO             = 'Cancelado';
    const DEVOLVIDO             = 'Devolvido';
    const COMPLETO              = 'Completo';
    const ESTORNADO             = 'Estornado';
}

require_once 'app/Mage.php';
require_once 'app/code/core/Mage/Sales/Model/Order.php';

$codigoTransacao  = $_POST["transacao_id"];
$statusAkatus     = $_POST["status"];
$tokenRecebido    = $_POST["token"];

$order = getOrder($codigoTransacao);
$tokenNIP = Mage::getStoreConfig('payment/akatus/tokennip', $order->getStoreId());

if($tokenNIP == $tokenRecebido) {
    $newOrderState = getNewOrderState($statusAkatus, $order);
    Mage::Log('new order state: ' . $newOrderState);
    if ($newOrderState) {
        updateOrder($order, $newOrderState);
    }
}

function getOrder($codigoTransacao)
{
    $mageRunCode = isset($_SERVER ['MAGE_RUN_CODE'] ) ? $_SERVER ['MAGE_RUN_CODE'] : '';
    $mageRunType = isset($_SERVER ['MAGE_RUN_TYPE'] ) ? $_SERVER ['MAGE_RUN_TYPE'] : 'store';
    Mage::app($mageRunCode, $mageRunType);

    $db = Mage::getSingleton('core/resource')->getConnection('core_write');        

    $retorno = $db->query("SELECT idpedido FROM akatus_transacoes WHERE codtransacao = '".$codigoTransacao."' ORDER BY id DESC");
    $transacao = $retorno->fetch();        

    if ($order = Mage::getModel('sales/order')->load($transacao['idpedido'])) {
        return $order;
    } else {
        return Mage::getModel('sales/order')->loadByIncrementId($transacao['idpedido']);
    }
}

function getNewOrderState($statusAkatus, $order)
{
    $currentOrderState = $order->getState();

    switch ($statusAkatus) {

        case StatusTransacaoAkatus::APROVADO:
            $statusList = array(
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW
            );

            if (in_array($currentOrderState, $statusList)) {
                return Mage_Sales_Model_Order::STATE_PROCESSING;
            } else {
                return false;
            }                

        case StatusTransacaoAkatus::CANCELADO:
            $statusList = array(
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW,
                Mage_Sales_Model_Order::STATE_HOLDED
            );                

            if (in_array($currentOrderState, $statusList)) {
                return Mage_Sales_Model_Order::STATE_CANCELED;
            } else {
                return false;
            }                

        case StatusTransacaoAkatus::DEVOLVIDO:
            $statusList = array(
                Mage_Sales_Model_Order::STATE_PROCESSING,
                Mage_Sales_Model_Order::STATE_COMPLETE                
            );                

            if (in_array($currentOrderState, $statusList)) {
                return Akatus_Akatus_Model_Order::STATE_REFUNDED;                    
            } else {
                return false;
            }

	 case StatusTransacaoAkatus::ESTORNADO:
            $statusList = array(
                Mage_Sales_Model_Order::STATE_PROCESSING,
                Mage_Sales_Model_Order::STATE_COMPLETE                
            );

            if (in_array($currentOrderState, $statusList)) {
                return Akatus_Akatus_Model_Order::STATE_REFUNDED;                    
            } else {
                return false;
            }

        case StatusTransacaoAkatus::AGUARDANDO_PAGAMENTO:
            $statusList = array(
                Mage_Sales_Model_Order::STATE_NEW
            );                

            if (in_array($currentOrderState, $statusList)) {
                return Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
            } else {
                return false;
            }

        case StatusTransacaoAkatus::EM_ANALISE:
            $statusList = array(
                Mage_Sales_Model_Order::STATE_NEW,
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT
            );                

            if (in_array($currentOrderState, $statusList)) {
                return Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
            } else {
                return false;
            }                                                

        default:
            return false;                    
    } 
}

function updateOrder($order, $newOrderState) {

    if ($newOrderState === Mage_Sales_Model_Order::STATE_CANCELED) {

        $order->getPayment()->cancel();
        cancelar($order);
    
        $stateAndStatus = Mage_Sales_Model_Order::STATE_CANCELED;
        $order->setState($stateAndStatus, $stateAndStatus, 'Cancelado na Akatus');
        $order->setStatus($stateAndStatus);


    } else if (podeEstornar($order) && $newOrderState === Akatus_Akatus_Model_Order::STATE_REFUNDED) {

        $order->getPayment()->cancel();
        cancelar($order);
        $order->setTotalRefunded($order->getTotalPaid());

        $stateAndStatus = Mage_Sales_Model_Order::STATE_CANCELED;
        $order->setState($stateAndStatus, $stateAndStatus, 'Estornado na Akatus');
        $order->setStatus($stateAndStatus);
        
    } else if ($order->getTotalPaid() == 0 && $newOrderState === Mage_Sales_Model_Order::STATE_PROCESSING) {

        $invoice = $order->prepareInvoice();
        $invoice->register()->capture();            
        Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();
    } else {
    
        $stateAndStatus = $newOrderState;
        $order->setState($stateAndStatus, $stateAndStatus);
        $order->setStatus($stateAndStatus);
    }

    $order->save();
}

function podeEstornar($order)
{
    $allInvoiced = true;

    foreach ($order->getAllItems() as $item) {
        if ($item->getQtyToInvoice()) {
            $allInvoiced = false;
            break;
        }
    }

    return $allInvoiced;
}

function cancelar($order)
{
    foreach ($order->getAllItems() as $item) {
        $item->setQtyCanceled($item->getQtyOrdered());
        $item->setTaxCanceled($item->getTaxCanceled() + $item->getBaseTaxAmount() * $item->getQtyCanceled() / $item->getQtyOrdered());
        $item->setHiddenTaxCanceled($item->getHiddenTaxCanceled() + $item->getHiddenTaxAmount() * $item->getQtyCanceled() / $item->getQtyOrdered());

        if ( ! $item->getIsVirtual())  {
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getProductId());
            $stockItem->addQty($item->getQtyOrdered());
            $stockItem->setIsInStock(true)->setStockStatusChangedAutomaticallyFlag(true);
            $stockItem->save();
        }
    }

    $order->setSubtotalCanceled($order->getSubtotal() - $order->getSubtotalInvoiced());
    $order->setBaseSubtotalCanceled($order->getBaseSubtotal() - $order->getBaseSubtotalInvoiced());

    $order->setTaxCanceled($order->getTaxAmount() - $order->getTaxInvoiced());
    $order->setBaseTaxCanceled($order->getBaseTaxAmount() - $order->getBaseTaxInvoiced());

    $order->setShippingCanceled($order->getShippingAmount() - $order->getShippingInvoiced());
    $order->setBaseShippingCanceled($order->getBaseShippingAmount() - $order->getBaseShippingInvoiced());

    $order->setDiscountCanceled(abs($order->getDiscountAmount()) - $order->getDiscountInvoiced());
    $order->setBaseDiscountCanceled(abs($order->getBaseDiscountAmount()) - $order->getBaseDiscountInvoiced());

    $order->setTotalCanceled($order->getGrandTotal() - $order->getTotalPaid());
    $order->setBaseTotalCanceled($order->getBaseGrandTotal() - $order->getBaseTotalPaid());
}
