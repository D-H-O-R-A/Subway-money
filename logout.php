<?php
session_start();

// Destruir todas as variáveis de sessão
$_SESSION = array();

session_destroy();

// Se desejar excluir a sessão, descomente a linha abaixo
// session_destroy();

// Redirecionar para uma página após a destruição da sessão
header("Location: index.php");
exit();
?>
