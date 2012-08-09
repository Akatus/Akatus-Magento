<?php
	#instancia o Mage
	require_once 'app/Mage.php';
        
	Mage::app('default');
	
        #abre conexao com o banco de dados
	$db = Mage::getSingleton('core/resource')->getConnection('core_write');
       
        #inicializacao de variaveis
	$orderId=0;	
	$CodigoTransacao=$_POST["transacao_id"];
	$StatusTransacao=$_POST["status"];
	$tokenRecebido =  $_POST["token"];
	
	$StatusNovo="";

        
	#resgata o numero da order atrelado a transacao
	$retorno=$db->query("SELECT idpedido FROM akatus_transacoes WHERE codtransacao = '".$CodigoTransacao."'");
       
	while ($row = $retorno->fetch() )
	{
		$orderId = $row['idpedido'];
	}
	
       // echo "OrderId obtido:" .$orderId;
        
	#faz a conferencia da transacao
	$tokennip=Mage::getStoreConfig('payment/akatus/tokennip');
	        
	//validao retorno
	if($tokennip == $tokenRecebido)
	{
            //echo "<br />Tokenip OK!";
       
		/*
	 		* Altera o Status do Pedido
			STATE_NEW             = 'new';				STATE_PENDING_PAYMENT = 'pending_payment';
    		STATE_PROCESSING      = 'processing';    	STATE_COMPLETE        = 'complete';
    		STATE_CLOSED          = 'closed';	    	STATE_CANCELED        = 'canceled';
    		STATE_HOLDED          = 'holded';	    	STATE_PAYMENT_REVIEW  = 'payment_review';
    	*/	
            
           /// echo "Alterando Status da transacao para inserir no BD do magento. Entra: ".$StatusTransacao. " deve ser salvo ";
            
		switch ($StatusTransacao) {
    		case "Aguardando Pagamento":
    	    	$StatusNovo="pending_payment";
        		break;
    		case "Aprovado":
        		$StatusNovo="complete";
        		break;
    		case "Em Análise":
        		$StatusNovo="pending_payment";
        		break;
    		case "Cancelado":
        		$StatusNovo="canceled";
        		break;
    		case "Devolvido":
        		$StatusNovo="canceled";
        		break;
    		case "Completo":
        		$StatusNovo="complete";
        		break;                        
    		default:
                        $StatusNovo="processing";	
		}
		//echo $StatusNovo . ".";
		#altera status do pedido
		$order = Mage::getModel('sales/order')->load($orderId);
                      	
                print_r($order);
		$order->setStatus($StatusNovo);
               
		$order->save();
               
	}
        
       
      