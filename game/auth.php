<?php 
session_start();
error_reporting(E_ALL);

// Mostra os erros
ini_set('display_errors', 1);
if (!isset($_SESSION['email'])) {
    http_response_code(401);
    return;
}

require('./../conectarbanco.php');
$conn = new mysqli(config['db_host'], config['db_user'], config['db_pass'], config['db_name']);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}


$stmt = $conn->prepare("SELECT * FROM appconfig where email = ?");
$stmt->bind_param("s", $_SESSION['email']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if(count($row) < 1){
    http_response_code(401);
    return;
}

// Recebendo e validando os valores
$bet = isset($_REQUEST['bet']) ? floatval($_REQUEST['bet']) : 0;
$acumulado = isset($_REQUEST['val']) ? floatval($_REQUEST['val']) : 0;
$type = (isset($_REQUEST['type']) && $_REQUEST['type'] == 'win');

// Verificando se o saldo é numérico e não nulo
$saldoAtual = isset($row['saldo']) && is_numeric($row['saldo']) ? $row['saldo'] : 0;

// Cálculo do novo saldo
$saldo = ($type ? floatval($saldoAtual) + $acumulado : floatval($saldoAtual) - $acumulado);

$stmt = $conn->prepare("UPDATE appconfig SET saldo = ? WHERE email = ?");
$stmt->bind_param("ds", $saldo, $_SESSION['email']);
$stmt->execute();

$stmt->close();
$conn->close();

http_response_code(200);
echo json_encode(array("saldo" => $saldo));
die;