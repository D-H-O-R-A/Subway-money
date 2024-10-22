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

// Preparar e executar a consulta
$stmt = $conn->prepare("SELECT * FROM app limit 1");
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(array("gameVelocity" => $row['dificuldade_jogo']));
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Configuração não encontrada"));
}

// Fechar a conexão
$stmt->close();
$conn->close();
