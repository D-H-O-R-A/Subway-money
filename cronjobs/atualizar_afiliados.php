<?php
session_start();

include_once './../conectarbanco.php';

$conn = new mysqli(config['db_host'], config['db_user'], config['db_pass'], config['db_name']);
// Verifica se houve algum erro na conexão
if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

// Obter todos os IDs distintos da tabela appconfig
$allIds = array();
$getAllIdsQuery = "SELECT DISTINCT id FROM appconfig";
$allIdsResult = $conn->query($getAllIdsQuery);

while ($row = $allIdsResult->fetch_assoc()) {
    $allIds[] = $row['id'];
}

$allIdsResult->close();

// Iterar sobre todos os IDs e atualizar a coluna indicados
foreach ($allIds as $id) {
    // Contar a ocorrência do ID na coluna lead_aff
    $countQuery = "SELECT COUNT(*) as count FROM appconfig WHERE FIND_IN_SET(?, lead_aff) > 0";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param("i", $id);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $countRow = $countResult->fetch_assoc();
    $count = $countRow['count'];
    $countStmt->close();

    // Atualizar a coluna indicados com o valor contado
    $updateQuery = "UPDATE appconfig SET indicados = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ii", $count, $id);
    $updateStmt->execute();
    $updateStmt->close();
}

$conn->close();
?>
