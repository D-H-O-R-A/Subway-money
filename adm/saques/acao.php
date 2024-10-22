<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once './../../islogadoadm.php';
require_once './../../conectarbanco2.php';

class SaquesAcao 
{
    public function __construct()
    {
        $acao = $_REQUEST['acao'] ?? null;
        if($acao && method_exists($this, $acao) ){
            $this->$acao();
        }else{
            header('Location: ./index.php');
        }
    }

    public function aprovar()
    {
        $id = $_REQUEST['id'] ?? null;
        if(!$id){
            return;
        }
        $db = new Database();
        $saque = $db->select('solicitacoes_de_saque', ['ID' => $id])[0];
        $gateway = $db->select('gateway', ['ID' => 1])[0];
    
        $payload = array(
            'value' => $saque['valor_solicitado'],
            'key' => $saque['chave_pix'],
            'typeKey' => 'document',
            'callbackUrl' => 'https://subwayoriginal.online/webhook/pixsaque.php'
        );
    
        $url = 'https://ws.suitpay.app/api/v1/gateway/pix-payment';
        $method = 'POST';
        
        $client_id = $gateway['client_id'];
        $client_secret = $gateway['client_secret'];

        $headers = array(
            "Content-Type: application/json",
            "ci: $client_id",
            "cs: $client_secret"
        );
    
        $ch = curl_init($url);
    
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result, true);
	//print_r($result);die;
        if(isset($result['idTransaction'])){
            $db->update('solicitacoes_de_saque', [ 'id_webhook' => $result['idTransaction'],'status'=>'PROCESSANDO' ], ['ID' => $id]);
        }else{
            die('Erro ao saque PIX');
        }
     
    
        $_SESSION['msgSaque'] = 'Saque aprovado com sucesso! em breve o valor será creditado na conta do usuário.';
        header('Location: ./index.php');
        die;
    }

    public function recusar()
    {
        $id = $_REQUEST['id'] ?? null;
        if(!$id){
            return;
        }

        $db = new Database();
        $saque = $db->select('solicitacoes_de_saque', ['ID' => $id])[0];
        $usuario = $db->select('appconfig', ['email' => $saque['email']])[0];

        $valor = (float)$saque['valor_solicitado'];
        $saldo = (float)$usuario['saldo'];

        $saldo += $valor;

        $db->update('appconfig', ['saldo' => $saldo], ['email' => $saque['email']]);
        $db->update('solicitacoes_de_saque', ['status' => 'RECUSADO'], ['ID' => $id]);

        header('Location: ./index.php');
        die;

    }
}

$data = new SaquesAcao();
unset($data);
