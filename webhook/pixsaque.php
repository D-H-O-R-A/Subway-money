<?php

# if is not a post request, exit
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    write_log('Solicitação inválida - Método não é POST');
    exit;
}

function bad_request()
{
    http_response_code(400);
    exit;
}

function write_log($message) {
    $logFile = './logpixsaque.log'; // Especifique o caminho para o arquivo de log
    $timeStamp = date('Y-m-d H:i:s');
    $logMessage = $timeStamp . ' ' . $message . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

function type($tipo){
    /*
       PAID_OUT, CANCELED, UNPAID, CHARGEBACK, WAITING_FOR_APPROVAL, PAYMENT_ACCEPT
    */
    switch($tipo){
        case 'PAID_OUT':
            return 'PAGO';
        case 'CANCELED':
            return 'CANCELADO';
        case 'UNPAID':
            return 'NÃO PAGO';
        case 'CHARGEBACK':
            return 'CHARGEBACK';
        case 'WAITING_FOR_APPROVAL':
            return 'AGUARDANDO APROVAÇÃO';
        case 'PAYMENT_ACCEPT':
            return 'PAGAMENTO ACEITO';
        default:
            return 'ERRO';
    }
}


write_log('Rota chamada com o método ' . $_SERVER['REQUEST_METHOD']);


# get the payload
$payload = file_get_contents('php://input');

# decode the payload
$payload = json_decode($payload, true);


write_log('body ->' . json_encode($payload));



# if the payload is not valid json, exit
if (is_null($payload)) {
    bad_request();
}

# if the payload is not a pix payment, exit
if ($payload['typeTransaction'] !== 'PIX_CASHOUT') {
    write_log('Solicitação inválida - nao é PIX');
    bad_request();
}


$externalReference = $payload['idTransaction'];
$status = $payload['statusTransaction'];


try {
    include_once './../conectarbanco2.php';
    $conn = new Database();
    $result = $conn->select('solicitacoes_de_saque',['id_webhook'=>$externalReference])[0];


    write_log('externalReference - '.$externalReference);
    write_log('status - '.$status);


    # if the payment is not found, exit
    if (!$result) {
        bad_request();
    }

    # if the payment is already confirmed, exit
   /* if ($result['status_webhook'] === 'PAID_OUT') {
        bad_request();
    }*/

    # if the payment is confirmed
    if ($status === 'PAID_OUT') {
        
        # update the payment status]
        $conn->update('solicitacoes_de_saque', ['status_webhook' => $status, 'data_webhook' => date('Y-m-d H:i:s'), 'status'=>'PAGO'], ['id_webhook' => $externalReference]);
        
        # return a success response
        var_dump(json_encode(array('success' => true, 'message' => 'PIX Enviado!.')));
        http_response_code(200);
        exit;
    }else{
        # update the payment status]
        $conn->update('solicitacoes_de_saque', ['status_webhook' => $status, 'data_webhook' => date('Y-m-d H:i:s'), 'status'=>type($status)], ['id_webhook' => $externalReference]);
        var_dump(json_encode(array('success' => false, 'message' => 'Ops.. PIX não aprovado!.')));
        http_response_code(200);
        exit;
    }

} catch (\Exception $th) {
    write_log('body ->' . $th->getMessage());
}


