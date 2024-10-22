<?php
// processar_subtracao.php

session_start();
if (!isset($_SESSION['email'])) {
    header("Location:../");
    exit();
}

// Verifica se o valor a subtrair foi enviado
if (isset($_POST['valorSubtrair'])) {
    // Valor a subtrair do saldo
    $valorSubtrair = floatval($_POST['valorSubtrair']);
    $valorSubtrair2 = floatval($_POST['valorSubtrair'] * 100);
    $_SESSION['valorSubtrair'] = $valorSubtrair;
    
    include_once './../conectarbanco.php';

    $conn = new mysqli(config['db_host'], config['db_user'], config['db_pass'], config['db_name']);

    // Verifica se a conexão foi bem-sucedida
    if ($conn->connect_error) {
        die("Falha na conexão com o banco de dados: " . $conn->connect_error);
    }

    // Recupera o email da sessão
    $email = $_SESSION['email'];

    // Consulta para obter o saldo atual e a coluna "percas"
    $consulta_saldo_perdas = "SELECT saldo, percas FROM appconfig WHERE email = '$email'";
    $resultado_saldo_perdas = $conn->query($consulta_saldo_perdas);

    // Verifica se a consulta foi bem-sucedida
    if ($resultado_saldo_perdas) {
        // Obtém o saldo e percas atuais
        $row = $resultado_saldo_perdas->fetch_assoc();
        $saldo_atual = $row['saldo'];
        $percas_atual = $row['percas'];

        // Atualiza a coluna "percas" no banco de dados
        $atualizar_percas = "UPDATE appconfig SET percas = percas + $valorSubtrair WHERE email = '$email'";
        $resultado_atualizar_percas = $conn->query($atualizar_percas);

        // Verifica se a atualização da coluna "percas" foi bem-sucedida
        if ($resultado_atualizar_percas) {
            // Verifica se o saldo é suficiente para a subtração
            if ($saldo_atual >= $valorSubtrair) {
                // Atualiza o saldo no banco de dados
                $atualizar_saldo = "UPDATE appconfig SET saldo = saldo - $valorSubtrair WHERE email = '$email'";
                $resultado_atualizar_saldo = $conn->query($atualizar_saldo);

                // Verifica se a atualização do saldo foi bem-sucedida
                if ($resultado_atualizar_saldo) {
                    // Verifica se o email já existe na tabela "game"
                    $verificar_email = "SELECT * FROM game WHERE email = '$email'";
                    $resultado = $conn->query($verificar_email);

                    if ($resultado->num_rows > 0) {
                        // O email já existe, então atualiza apenas o entry_value
                        $atualizar_game = "UPDATE game SET entry_value = $valorSubtrair2, out_value = 0 WHERE email = '$email'";
                        $conn->query($atualizar_game);
                    } else {
                        // O email não existe, então insere um novo registro na tabela "game"
                        $inserir_game = "INSERT INTO game (entry_value, email) VALUES ($valorSubtrair2, '$email')";
                        $conn->query($inserir_game);
                    }

                    // Consulta para obter o novo saldo
                    $consulta_saldo = "SELECT saldo FROM appconfig WHERE email = '$email'";
                    $resultado_saldo = $conn->query($consulta_saldo);

                    // Verifica se a consulta foi bem-sucedida
                    if ($resultado_saldo) {
                        // Obtém o novo saldo
                        $row = $resultado_saldo->fetch_assoc();
                        $novo_saldo = $row['saldo'];

                        // Imprime o novo saldo
                        echo number_format($novo_saldo, 2, ',', '.');

                        // Redireciona para outra página
                        header("Location: ../jogar/");
                        exit();
                    } else {
                        // Se a consulta falhar, imprime o erro
                        echo "Erro na consulta: " . $conn->error;
                    }
                } else {
                    // Se a atualização do saldo falhar, imprime o erro
                    echo "Erro na atualização do saldo: " . $conn->error;
                }
            } else {
                // Saldo insuficiente, imprime aviso
                echo "Saldo insuficiente. Valor a subtrair é maior que o saldo atual.";
                header("Location: ../deposito");
            }
        } else {
            // Se a atualização da coluna "percas" falhar, imprime o erro
            echo "Erro na atualização da coluna 'percas': " . $conn->error;
        }
    } else {
        // Se a consulta falhar, imprime o erro
        echo "Erro na consulta: " . $conn->error;
    }

    // Fecha a conexão com o banco de dados
    $conn->close();
}
?>
