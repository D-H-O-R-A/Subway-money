<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../");
    exit();
}
include_once './../conectarbanco.php';

$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$msg = $_GET['msg'] ?? '';
if (!empty($email)) {
    try {
        $dbuser = config['db_user'];
        $dbpass = config['db_pass'];
        $dbname = config['db_name'];
        $dbhost = config['db_host'];

        $conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
        $conn = new PDO("mysql:host={$dbhost};dbname={$dbname}", $dbuser, $dbpass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verifica se o email existe na tabela confirmar_deposito
        $stmt = $conn->prepare("SELECT * FROM confirmar_deposito WHERE email = :email AND status = 'pendente'");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Loop através de todas as entradas com o mesmo email e status pendente
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Verifica se há uma correspondência na tabela pix_deposito
            $stmtPix = $conn->prepare("SELECT * FROM pix_deposito WHERE code = :externalReference");
            $stmtPix->bindParam(':externalReference', $result['externalreference']);
            $stmtPix->execute();

            // Verifica se há uma correspondência na tabela pix_deposito
            $resultPix = $stmtPix->fetch(PDO::FETCH_ASSOC);

            if ($resultPix !== false) {
                // Atualiza o status para 'aprovado' na tabela confirmar_deposito
                $updateStmt = $conn->prepare("UPDATE confirmar_deposito SET status = 'aprovado' WHERE externalreference = :externalReference");
                $updateStmt->bindParam(':externalReference', $result['externalreference']);
                $updateStmt->execute();

                // Obtém o valor da correspondência na tabela pix_deposito
                $valorCorrespondencia = $resultPix['value'];

                // Atualiza a coluna saldo na tabela appconfig
                $updateSaldoStmt = $conn->prepare("UPDATE appconfig SET saldo = saldo + :valorCorrespondencia, depositou = depositou + :valorCorrespondencia WHERE email = :email");
                $updateSaldoStmt->bindParam(':valorCorrespondencia', $valorCorrespondencia);
                $updateSaldoStmt->bindParam(':email', $email);
                $updateSaldoStmt->execute();
                break; // Sai do loop assim que encontrar uma correspondência
            }
        }
    } catch (PDOException $e) {
        // Trata a exceção, se necessário
        echo "Erro: " . $e->getMessage();
    }
} else {
    // O código que você quer executar se o email estiver vazio
}
?>








<?php
// Inicie a sessão se ainda não foi iniciada


$dbname = config['db_name'];
$dbuser = config['db_user'];
$dbpass =  config['db_pass'];

$host = config['db_host'];  // Altere se o banco estiver em um servidor diferente

// Conecte-se ao banco de dados
$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

// Verifique se a conexão foi bem-sucedida
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

// Recupere o email da sessão
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    // Consulta para obter o saldo associado ao email na tabela appconfig
    $consulta_saldo = "SELECT saldo FROM appconfig WHERE email = '$email'";

    // Execute a consulta
    $resultado_saldo = $conn->query($consulta_saldo);

    // Verifique se a consulta foi bem-sucedida
    if ($resultado_saldo) {
        // Verifique se há pelo menos uma linha retornada
        if ($resultado_saldo->num_rows > 0) {
            // Obtenha o saldo da primeira linha
            $row = $resultado_saldo->fetch_assoc();
            $saldo = $row['saldo'];
        }
    }
}

// Feche a conexão com o banco de dados
$conn->close();
?>





<!DOCTYPE html>

<html lang="pt-br" class="w-mod-js w-mod-ix wf-spacemono-n4-active wf-spacemono-n7-active wf-active">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style>
        .wf-force-outline-none[tabindex="-1"]:focus {
            outline: none;
        }
    </style>
    <meta charset="pt-br">
    <title>SubwayPay 🌊 </title>
    <meta property="og:image" content="../img/logo.png">
    <meta content="SubwayPay 🌊" property="og:title">
    <meta name="twitter:image" content="../img/logo.png">

    <meta content="width=device-width, initial-scale=1" name="viewport">
    <link href="arquivos/page.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>    


    <script type="text/javascript">
        WebFont.load({
            google: {
                families: ["Space Mono:regular,700"]
            }
        });
    </script>




    <link rel="apple-touch-icon" sizes="180x180" href="../img/logo.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/logo.png">


    <link rel="icon" type="image/x-icon" href="../img/logo.png">
    <link rel="stylesheet" href="arquivos/css" media="all">

</head>

<body>
    <?php
        if ($msg != '') {
            echo '<script>
            Swal.fire({
                icon: "success",
                title: "Eba!",
                text: "'.$msg.'",
                showConfirmButton: false,
                timer: 2500
            });
            </script>';
        }
    ?>


    <div>
        <div data-collapse="small" data-animation="default" data-duration="400" role="banner" class="navbar w-nav">
            <div class="container w-container">
                <a href="../painel" aria-current="page" class="brand w-nav-brand" aria-label="home">
                    <img src="arquivos/l2.png" loading="lazy" height="28" alt="" class="image-6">
                    <div class="nav-link logo">SubwayPay</div>
                </a>





                <nav role="navigation" class="nav-menu w-nav-menu">
                    <a href="../painel" class="nav-link w-nav-link" style="max-width: 940px;">Jogar</a>
                    <a href="../saque" class="nav-link w-nav-link" style="max-width: 940px;">Saque</a>

                    <a href="../afiliate/" class="nav-link w-nav-link" style="max-width: 940px;">Indique e ganhe</a>
                    <a href="../logout.php" class="nav-link w-nav-link" style="max-width: 940px;">Sair</a>

                    <a href="../deposito/" class="button nav w-button">Depositar</a>
                </nav>



                <style>
                    .nav-bar {
                        display: none;
                        background-color: #333;
                        /* Cor de fundo do menu */
                        padding: 20px;
                        /* Espaçamento interno do menu */
                        width: 90%;
                        /* Largura total do menu */

                        position: fixed;
                        /* Fixa o menu na parte superior */
                        top: 0;
                        left: 0;
                        z-index: 1000;
                        /* Garante que o menu está acima de outros elementos */
                    }

                    .nav-bar a {
                        color: white;
                        /* Cor dos links no menu */
                        text-decoration: none;
                        padding: 10px;
                        /* Espaçamento interno dos itens do menu */
                        display: block;
                        margin-bottom: 10px;
                        /* Espaçamento entre os itens do menu */
                    }

                    .nav-bar a.login {
                        color: white;
                        /* Cor do texto para o botão Login */
                    }

                    .button.w-button {
                        text-align: center;
                    }
                </style>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var menuButton = document.querySelector('.menu-button');
                        var navBar = document.querySelector('.nav-bar');

                        menuButton.addEventListener('click', function() {
                            // Toggle the visibility of the navigation bar
                            if (navBar.style.display === 'block') {
                                navBar.style.display = 'none';
                            } else {
                                navBar.style.display = 'block';
                            }
                        });
                    });
                </script>



                <style>
                    .menu-button2 {
                        border-radius: 15px;
                        background-color: #000;
                    }
                </style>




                <div class="w-nav-button" style="-webkit-user-select: text;" aria-label="menu" role="button" tabindex="0" aria-controls="w-nav-overlay-0" aria-haspopup="menu" aria-expanded="false">
                    <div class="" style="-webkit-user-select: text;">


                        <a href="../deposito/" class="menu-button2 w-nav-dep nav w-button">DEPOSITAR</a>
                    </div>
                </div>
                <div class="menu-button w-nav-button" style="-webkit-user-select: text;" aria-label="menu" role="button" tabindex="0" aria-controls="w-nav-overlay-0" aria-haspopup="menu" aria-expanded="false">
                    <div class="icon w-icon-nav-menu"></div>
                </div>
            </div>
            <div class="w-nav-overlay" data-wf-ignore="" id="w-nav-overlay-0"></div>
        </div>
        <div class="nav-bar">
            <a href="../painel/" class="button w-button">
                <div>Jogar</div>
            </a>
            <a href="../saque/" class="button w-button">
                <div>Saque</div>
            </a>

            <a href="../afiliate/" class="button w-button">
                <div>Indique & ganhe</div>
            </a>
            <a href="../logout.php" class="button w-button">
                <div>Sair</div>
            </a>
            <a href="../deposito/" class="button w-button">Depositar</a>
        </div>








        <div id="saldoDiv" style="position: absolute; top: 100px; width: 100%; line-height: 26px; color: #fff; z-index: 10; text-align: center;">
            SALDO: R$<b class="saldo"> <?php echo isset($saldo) ? number_format($saldo, 2, ',', '.') : '0,00'; ?> </b>
        </div>



        <script>
            // Script JavaScript para exibir o saldo
            window.onload = function() {
                var saldoDiv = document.getElementById('saldoDiv');

                <?php if (isset($saldo)) : ?>
                    saldoDiv.innerHTML = 'SALDO: R$<b class="saldo">' + <?php echo json_encode(number_format($saldo, 2, ',', '.')); ?> + '</b>';
                <?php endif; ?>
            };
        </script>



        <script>
            function subtrairSaldo() {
                // Evitar cliques múltiplos
                var botaoSubtrair = document.getElementById('botaoSubtrair');
                if (botaoSubtrair.disabled) {
                    return;
                }

                // Desabilitar o botão após clicar
                botaoSubtrair.disabled = true;

                // Obtém o valor a subtrair do campo de entrada
                var valorSubtrair = document.getElementById('valorSubtrair').value;

                // Envia o valor para o script PHP de processamento
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'processar_subtracao.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            // Atualiza dinamicamente o saldo na página
                            var saldoDiv = document.getElementById('saldo');
                            saldoDiv.innerHTML = 'SALDO: R$<b class="saldo">' + xhr.responseText + '</b>';

                            // Redireciona para outra página
                            window.location.href = '../jogo';
                        } else {
                            // Trata possíveis erros
                            alert('Erro na solicitação: ' + xhr.statusText);
                            // Habilitar o botão novamente em caso de erro
                            botaoSubtrair.disabled = false;
                        }
                    }
                };
                xhr.send('valorSubtrair=' + valorSubtrair);
            }
        </script>










        <section id="hero" class="hero-section dark wf-section">

            <style>
                div.escudo {
                    display: block;
                    width: 247px;
                    line-height: 65px;
                    font-size: 12px;
                    margin: -60px 0 0 0;
                    background-image: url(/assets/img/game/escudo-branco.png);
                    background-size: contain;
                    background-repeat: no-repeat;
                    background-position: center;
                    filter: drop-shadow(1px 1px 3px #00000099) hue-rotate(0deg);
                }

                div.escudo img {
                    width: 50px;
                    margin: -10px 6px 0 0;
                }
            </style>

            <div class="minting-container w-container">
                <div class="escudo">
                    <a href="../ranking">Ranking</a>
                    <img src="arquivos/trophy.gif">
                    <a href="../painel/">Painel</a>
                </div>
                <h2>Iniciar corrida</h2>
                <p>Pronto para iniciar mais uma corrida?</p>
                <p>Escolha um valor para iniciar:</p>





                <form id="formSubtrair" action="./../painel/processar_subtracao.php" method="post" aria-label="Form">
                    <div class="">
                        <input type="submit" value="1,00" id="valorSubtrair" name="valorSubtrair" onclick="subtrairSaldo()" class="primary-button w-button"><br><br>
                    </div>
                </form>








                <form id="formSubtrair" action="./../painel/processar_subtracao.php" method="post" aria-label="Form">

                    <div class="">
                        <input type="submit" value="2,00" id="valorSubtrair" name="valorSubtrair" onclick="subtrairSaldo()" class="primary-button w-button"><br><br>
                    </div>
                </form>







                <form id="formSubtrair" action="./../painel/processar_subtracao.php" method="post" aria-label="Form">

                    <div class="">
                        <input type="submit" value="5,00" id="valorSubtrair" name="valorSubtrair" onclick="subtrairSaldo()" class="primary-button w-button"><br><br>
                    </div>
                </form>






                <p>Você pode Tentar Gratis! </p>



                <form data-name="" id="auth" method="post" aria-label="Form" action="../jogodemo">
                    <div class="">
                        <input type="submit" value="Testar" class="primary-button w-button"><br><br>
                    </div>
                </form>





            <i style="font-size: 10px;">Sua meta(ganho) é 3x o valor apostado!</i>
            </div>
            <div id="wins" style="
                display: block;
                width: 240px;
                font-size: 12px;
                padding: 5px 0;
                text-align: center;
                line-height: 13px;
                background-color: #FFC107;
                border-radius: 10px;
                border: 3px solid #1f2024;
                box-shadow: -3px 3px 0 0px #1f2024;
                margin: -24px auto 0 auto;
                z-index: 1000;
            ">estef.rodri1<br class="jWQDfMST8B">Ganhou R$ 4.25</div>
        </section>
        <section id="mint" class="mint-section wf-section">
            <div class="minting-container w-container">
                <img src="arquivos/money.png" loading="lazy" width="240" alt="" class="mint-card-image">
                <h2>SubwayPay</h2>
                <p class="paragraph">Bem-vindo ao mundo emocionante de SubwayPay!
                    Prepare-se para uma aventura eletrizante nos trilhos, onde cada curva guarda a promessa de fortuna.
                    Desvie dos obstáculos, colete moedas reluzentes e desbloqueie novos percursos enquanto corre em
                    busca da riqueza. Sua jornada pela cidade começa agora – acelere, desfrute e acumule sua fortuna nos
                    trilhos de SubwayPay!. </p>


                <a href="../painel" class="primary-button w-button w--current">JOGAR AGORA</a>




                <div class="price">
                    <strong>Rodadas de boas vindas disponível</strong>
                </div>
            </div>
        </section>
        <div class="intermission wf-section">
            <div data-w-id="aa174648-9ada-54b0-13ed-6d6e7fd17602" class="center-image-block">
                <img src="arquivos/" loading="eager" alt="">
            </div>
            <div data-w-id="6d7abe68-30ca-d561-87e1-a0ecfd613036" class="center-image-block _2">
                <img src="arquivos/" loading="eager" alt="">
            </div>
            <div data-w-id="e04b4de1-df2a-410e-ce98-53cd027861f6" class="center-image-block _2">
                <img src="arquivos/" loading="eager" alt="" class="image-3">
            </div>
        </div>
    </div>
    <div id="faq" class="faq-section">
        <div class="faq-container w-container">
            <h2>faq</h2>
            <div class="question first">
                <img src="arquivos/" loading="lazy" width="110" alt="">
                <h3>Como funciona?</h3>
                <div>SubwayPay é o mais novo jogo divertido e lucrativo da galera! Lembra daquele joguinho de surfar
                    por cima dos trens que todo mundo era viciado? Ele voltou e agora dá para ganhar dinheiro de
                    verdade, mas cuidado com os obstáculos para você garantir o seu prêmio. É super simples, surf,
                    desvie dos obstáculos e colete seus prêmios.
                </div>
            </div>
            <div class="question">
                <img src="arquivos/60fa0061a0450e3b6f52e12f_Body.svg" loading="lazy" width="90" alt="">
                <h3>Como posso jogar?</h3>
                <div class="w-richtext">
                    <p>Você precisa fazer um depósito inicial na plataforma para começar a jogar e faturar.
                        Lembrando
                        que você indicando amigos, você ganhará dinheiro de verdade na sua conta bancária.</p>
                </div>
            </div>
            <div class="question">
                <img src="arquivos/61070a430f976c13396eee00_Gradient Shades.svg" loading="lazy" width="120" alt="">
                <h3>Como posso sacar? <br>
                </h3>
                <p>O saque é instantâneo. Utilizamos a sua chave PIX como CPF para enviar o pagamento, é na hora e
                    no
                    PIX. 7 dias por semana e 24 horas por dia. <br>
                </p>
            </div>
            <div class="question">
                <img src="arquivos/60fa004b7690e70dded91f9a_light.svg" loading="lazy" width="80" alt="">
                <h3>É tipo foguetinho?</h3>
                <div>
                    <b>Não</b>! SubwayPay é totalmente diferente, basta apenas estar atento para desviar dos
                    obstáculos na hora certa. Não existe sua sorte em jogo, basta ter foco e completar o percurso
                    até resgatar o máximo de moedas que conseguir.
                </div>
            </div>
            <div class="question">
                <img src="arquivos/60f8d0c69b41fe00d53e8807_Helmet.svg" loading="lazy" width="90" alt="">
                <h3>Existem eventos?</h3>
                <div class="w-richtext">
                    <ul role="list">
                        <li>
                            <strong>Jogatina</strong>. Quanto mais você correr, mais moedas você coleta e mais
                            dinheiro você ganha. Mas cuidado! Há trens escondidas entre as
                            ruas.
                        </li>
                        <li>
                            <strong>Torneios</strong>. Além disso, você pode competir com outros jogadores em
                            torneios e
                            desafios diários para ver quem consegue a maior pontuação e fatura mais dinheiro. A
                            emoção
                            da competição e a chance de ganhar grandes prêmios adicionam uma camada extra de
                            adrenalina
                            ao jogo.
                        </li>
                    </ul>
                    <p>Clique <a href="https://t.me/">aqui</a> e acesse nosso grupo no Telegram
                        para
                        participar de eventos exclusivos. </p>
                </div>
            </div>
            <div class="question last">
                <img src="arquivos/60f8d0c657c9a88fe4b40335_Exploded Head.svg" loading="lazy" width="72" alt="">
                <h3>Dá para ganhar mais?</h3>
                <div class="w-richtext">
                    <p>Chame um amigo para jogar e após o depósito e a primeira partida será creditado em sua conta
                        R$5
                        para sacar a qualquer momento. </p>
                    <ol role="list">
                        <li>O saldo é adicionado diretamente ao seu saldo em dinheiro, com o qual você pode jogar ou
                            sacar. </li>
                        <li>Seu amigo deve se inscrever através do seu link de convite pessoal. </li>
                        <li>Seu amigo deve ter depositado pelo menos R$25.00 BRL para receber o prêmio do convite.
                        </li>
                        <li>Você não pode criar novas contas na SubwayPay e se inscrever através do seu próprio link
                            para receber a recompensa. O programa Indique um Amigo é feito para nossos jogadores
                            convidarem amigos para a plataforma SubwayPay. Qualquer outro uso deste programa é
                            estritamente proibido. </li>
                    </ol>
                    <p>‍</p>
                </div>
            </div>
        </div>
        <div class="faq-left">
            <img src="arquivos/60f988c7c856f076b39f8fa4_head 04.svg" loading="eager" width="238.5" alt="" class="faq-img" style="opacity: 0;">
            <img src=".arquivos/60f988c9402afc1dd3f629fe_head 26.svg" loading="eager" width="234" alt="" class="faq-img _1" style="opacity: 0;">
            <img src="arquivos/60f988c9bc584ead82ad8416_head 29.svg" loading="lazy" width="234" alt="" class="faq-img _2" style="opacity: 0;">
            <img src="arquivos/60f988c913f0ba744c9aa13e_head 27.svg" loading="lazy" width="234" alt="" class="faq-img _3" style="opacity: 0;">
            <img src="arquivos/60f988c9d3d37e14794eca22_head 25.svg" loading="lazy" width="234" alt="" class="faq-img _1" style="opacity: 0;">
            <img src="arquivos/60f988c98b7854f0327f5394_head 24.svg" loading="lazy" width="234" alt="" class="faq-img _2" style="opacity: 0;">
            <img src="arquivos/60f988c82f5c199c4d2f6b9f_head 05.svg" loading="lazy" width="234" alt="" class="faq-img _3" style="opacity: 0;">
        </div>
        <div class="faq-right">
            <img src="arquivos/60f988c88b7854b5127f5393_head 23.svg" loading="eager" width="238.5" alt="" class="faq-img" style="opacity: 0;">
            <img src="arquivos/60f988c8bf76d754b9c48573_head 12.svg" loading="eager" width="234" alt="" class="faq-img _1" style="opacity: 0;">
            <img src="arquivos/60f988c8f2b58f55b60d858f_head 21.svg" loading="lazy" width="234" alt="" class="faq-img _2" style="opacity: 0;">
            <img src="arquivos/60f988c8e83a994a38909bc4_head 22.svg" loading="lazy" width="234" alt="" class="faq-img _3" style="opacity: 0;">
            <img src="arquivos/60f988c8a97a7c125d72046d_head 20.svg" loading="lazy" width="234" alt="" class="faq-img _1" style="opacity: 0;">
            <img src="arquivos/60f988c8fbbbfe5fc68169e0_head 14.svg" loading="lazy" width="234" alt="" class="faq-img _2" style="opacity: 0;">
            <img src="arquivos/60f988c88b7854b35e7f5390_head 18.svg" loading="lazy" width="234" alt="" class="faq-img _3" style="opacity: 0;">
        </div>
        <div class="faq-bottom">
            <img src="arquivos/60f988c8ba5339712b3317c0_head 16.svg" loading="lazy" width="234" alt="" class="faq-img _3" style="opacity: 0;">
            <img src="arquivos/60f988c86e8603bce1c16a98_head 17.svg" loading="lazy" width="234" alt="" class="faq-img" style="opacity: 0;">
            <img src="arquivos/60f988c889b7b12755035f2f_head 19.svg" loading="lazy" width="234" alt="" class="faq-img _1" style="opacity: 0;">
        </div>
        <div class="faq-top">
            <img src="arquivos/60f988c8a97a7ccf6f72046a_head 11.svg" loading="eager" width="234" alt="" class="faq-img _3" style="opacity: 0;">
            <img src="arquivos/60f988c7fbbbfed6f88169df_head 02.svg" loading="eager" width="234" alt="" class="faq-img" style="opacity: 0;">
            <img src="arquivos/60f8dbc385822360571c62e0_icon-256w.png" loading="eager" width="234" alt="" class="faq-img _1" style="opacity: 0;">
        </div>
    </div>

    <div class="footer-section wf-section">
        <div class="domo-text">SUBWAY <br>
        </div>
        <div class="domo-text purple">PAY <br>
        </div>
        <div class="follow-test">© Copyright xlk Limited, with registered
            offices at
            Dr. M.L. King
            Boulevard 117, accredited by license GLH-16289876512. </div>
        <div class="follow-test">
            <a href="../legal">
                <strong class="bold-white-link">Termos de uso</strong>
            </a>
        </div>
        <div class="follow-test">contato@subwaypay.cloud</div>
    </div>




    <div id="imageDownloaderSidebarContainer">
        <div class="image-downloader-ext-container">
            <div tabindex="-1" class="b-sidebar-outer"><!---->
                <div id="image-downloader-sidebar" tabindex="-1" role="dialog" aria-modal="false" aria-hidden="true" class="b-sidebar shadow b-sidebar-right bg-light text-dark" style="width: 500px; display: none;"><!---->
                    <div class="b-sidebar-body">
                        <div></div>
                    </div><!---->
                </div><!----><!---->
            </div>
        </div>
    </div>
    <div style="visibility: visible;">
        <div></div>
        <div>
            <div style="display: flex; flex-direction: column; z-index: 999999; bottom: 88px; position: fixed; right: 16px; direction: ltr; align-items: end; gap: 8px;">
                <div style="display: flex; gap: 8px;"></div>
            </div>
            <style>
                @-webkit-keyframes ww-c5d711d7-9084-48ed-a561-d5b5f32aa3a5-launcherOnOpen {
                    0% {
                        -webkit-transform: translateY(0px) rotate(0deg);
                        transform: translateY(0px) rotate(0deg);
                    }

                    30% {
                        -webkit-transform: translateY(-5px) rotate(2deg);
                        transform: translateY(-5px) rotate(2deg);
                    }

                    60% {
                        -webkit-transform: translateY(0px) rotate(0deg);
                        transform: translateY(0px) rotate(0deg);
                    }


                    90% {
                        -webkit-transform: translateY(-1px) rotate(0deg);
                        transform: translateY(-1px) rotate(0deg);

                    }

                    100% {
                        -webkit-transform: translateY(-0px) rotate(0deg);
                        transform: translateY(-0px) rotate(0deg);
                    }
                }

                @keyframes ww-c5d711d7-9084-48ed-a561-d5b5f32aa3a5-launcherOnOpen {
                    0% {
                        -webkit-transform: translateY(0px) rotate(0deg);
                        transform: translateY(0px) rotate(0deg);
                    }

                    30% {
                        -webkit-transform: translateY(-5px) rotate(2deg);
                        transform: translateY(-5px) rotate(2deg);
                    }

                    60% {
                        -webkit-transform: translateY(0px) rotate(0deg);
                        transform: translateY(0px) rotate(0deg);
                    }


                    90% {
                        -webkit-transform: translateY(-1px) rotate(0deg);
                        transform: translateY(-1px) rotate(0deg);

                    }

                    100% {
                        -webkit-transform: translateY(-0px) rotate(0deg);
                        transform: translateY(-0px) rotate(0deg);
                    }
                }

                @keyframes ww-c5d711d7-9084-48ed-a561-d5b5f32aa3a5-widgetOnLoad {
                    0% {
                        opacity: 0;
                    }

                    100% {
                        opacity: 1;
                    }
                }

                @-webkit-keyframes ww-c5d711d7-9084-48ed-a561-d5b5f32aa3a5-widgetOnLoad {
                    0% {
                        opacity: 0;
                    }

                    100% {
                        opacity: 1;
                    }
                }
            </style>
        </div>
    </div>
</body>
<script>
    let url = window.location.href.split('?')[0];
    window.history.replaceState({}, document.title, url);
    window.history.pushState({}, '', '/painel');             
</script>
</html>