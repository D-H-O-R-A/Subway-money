<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
session_start();

//echo $_SESSION['email']; die;

if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
} else {
    header("Location: ./../../saque");
    exit();
}

// Verifique se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Conexão com o banco de dados (substitua pelos detalhes de conexão reais)

    include_once './../conectarbanco.php';
    include_once './../conectarbanco2.php';
    
    $db = new Database();
    $usuario = $db->select('appconfig', ['email' => $email])[0];
    $app = $db->select('app', ['id'=>1])[0];

    // Dados do formulário
    $nomeDestinatario = $_POST["withdrawName"];
    $valorSaque = $_POST["withdrawValue"];
    $chavePIX = $_POST["withdrawCPF"];

    if($valorSaque > $usuario['saldo']){
        $msg = "Saldo insuficiente para realizar o saque.";
        header("Location: ./index.php?msg=$msg&error=true");
        die;
    }
    $conn = connect();
    

    // Execute as ações necessárias após a verificação bem-sucedida
    // Salve os dados relevantes para exibição no painel administrativo

    $valorSaqueReal = $valorSaque - ($valorSaque * $app['taxa_saque'] / 100);

    // Preparar a consulta para inserir a solicitação de saque
    $insertQuery = "INSERT INTO solicitacoes_de_saque (nome_usuario, chave_pix, valor_solicitado, email) VALUES (?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("ssss", $nomeDestinatario, $chavePIX, $valorSaqueReal, $email);
    if ($insertStmt->execute()) {
        // Subtrair o valor do saque do saldo disponível
        $saldoReal = ($usuario['saldo'] - $valorSaque);
        $updateQuery = "UPDATE appconfig SET saldo = ? WHERE email = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ds", $saldoReal, $email);
        $updateStmt->execute();


        $msg = "Saque de R$ " . $valorSaqueReal . " solicitado com sucesso! ADM irá analisar sua solicitação e em breve entrará em contato com você.!";
        header("Location: ./index.php?msg=$msg");
    } else {
        $msg = "Erro ao solicitar o saque. Por favor, tente novamente mais tarde.";
        header("Location: ./index.php?msg=$msg&error=true");
    }
    
   

    // Fechar as declarações e a conexão com o banco de dados
    $saldoStmt->close();
    $insertStmt->close();
    $updateStmt->close();
    $conn->close();
}else{
  header("Location: ./../../saque");
  exit();
}
?>

