<?php
session_start();

include_once './../conectarbanco.php';
$conn = connect();

// Verificar se o email está presente na sessão
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];



    $getLinkQuery = "SELECT plano FROM appconfig WHERE email = ?";
    $stmt = $conn->prepare($getLinkQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($plano);
    $stmt->fetch();
    $stmt->close();

    $getLinkQuery = "SELECT saldo_comissao FROM appconfig WHERE email = ?";
    $stmt = $conn->prepare($getLinkQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($saldo_comissao);
    $stmt->fetch();
    $stmt->close();


    // Consultar o valor da coluna linkafiliado para o email atual
    $getLinkQuery = "SELECT linkafiliado FROM appconfig WHERE email = ?";
    $stmt = $conn->prepare($getLinkQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($linkAfiliado);
    $stmt->fetch();
    $stmt->close();

    // Consultar o valor da coluna indicados para o email atual
    $getIndicadosQuery = "SELECT indicados FROM appconfig WHERE email = ?";
    $stmt = $conn->prepare($getIndicadosQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($indicados);
    $stmt->fetch();
    $stmt->close();
} else {
    // Redirecionar para a página de login se o email não estiver na sessão
    header("Location: /login");
    exit();
}

$conn->close();
?>



<!DOCTYPE html>

<html lang="pt-br" class="w-mod-js wf-spacemono-n4-active wf-spacemono-n7-active wf-active w-mod-ix">

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
    <script src="arquivos/webfont.js" type="text/javascript"></script>


    <script type="text/javascript">
        WebFont.load({
            google: {
                families: ["Space Mono:regular,700"]
            }
        });
    </script>



    <script type="text/javascript">
        ! function(o, c) {
            var n = c.documentElement,
                t = " w-mod-";
            n.className += t + "js", ("ontouchstart" in o || o.DocumentTouch && c instanceof DocumentTouch) && (n
                .className += t + "touch")
        }(window, document);
    </script>
    <link rel="apple-touch-icon" sizes="180x180" href="../img/logo.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/logo.png">


    <link rel="icon" type="image/x-icon" href="../img/logo.png">

    <link rel="stylesheet" href="arquivos/css" media="all">

</head>

<body>
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

                    <a href="../afiliate/" class="nav-link w-nav-link w--current" style="max-width: 940px;">Indique & Ganhe</a>
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

            </a>
            <a href="../afiliate/" class="button w-button">
                <div>Indique & Ganhe</div>
            </a>
            <a href="../logout.php" class="button w-button">
                <div>Sair</div>
            </a>
            <a href="../deposito/" class="button w-button">Depositar</a>
        </div>

        <section id="hero" class="hero-section dark wf-section">
            <div class="minting-container w-container">
                <img src="arquivos/image.gif" loading="lazy" width="240" data-w-id="6449f730-ebd9-23f2-b6ad-c6fbce8937f7" alt="Roboto #6340" class="mint-card-image">

                <h<h2>Divulgue & Ganhe</h2>
                    <p>Este é o resumo de seu resultado divulgando. <br>
                    <p>Seu link de divulgação é: <br>
                        <?php echo $linkAfiliado; ?>
                    </p>
                    <br>

                    <p>
                        <a id="copiarLinkBtn" class="primary-button dark w-button" onclick="copiarLink()">Copiar link de afiliado</a>
                    </p>

                    <br><br>

                    <script>
                        function copiarLink() {
                            var linkText = '<?php echo $linkAfiliado; ?>';
                            var input = document.createElement('textarea');
                            input.value = linkText;
                            document.body.appendChild(input);
                            input.select();
                            document.execCommand('copy');
                            document.body.removeChild(input);
                            alert('Link copiado para a área de transferência: ' + linkText);
                        }
                    </script>

                    <div class="properties">
                        <h3 class="rarity-heading">Extrato</h3>
                        <div class="rarity-row roboto-type">
                            <div class="rarity-number full">Contabilização pode levar algumas horas.</div>
                        </div>
                        <div class="rarity-row roboto-type">
                            <div class="rarity-number full">Saldo de comissões disponível: </div>


                            <div class="padded">R$<?php echo $saldo_comissao; ?></div>


                        </div>
                        <div class="w-layout-grid grid">
                            <div>
                                <div class="rarity-row blue">
                                    <div class="rarity-number">Cadastros</div>
                                    <div><?php echo $indicados; ?> cadastros</div>

                                </div>
                                <div class="rarity-row blue">
                                    <div class="rarity-number">Comissão:</div>
                                    <div>R$<?php echo $saldo_comissao; ?></div>
                                </div>



                            </div>
                            <div>


                                <div class="rarity-row blue">
                                    <div class="rarity-number">Comissão: </div>
                                    <div>
                                        <?php echo $plano; ?> %
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="grid-box">
                            <a href="../saque-afiliado" class="primary-button w-button">Sacar saldo disponível</a>
                            <a href="=====" target="_blank" class="primary-button dark w-button">Suporte para afiliados</a>
                        </div>
                        <br>

                    </div>
            </div>
        </section>



        <div class="intermission wf-section"></div>
        <div id="about" class="comic-book white wf-section">
            <div class="minting-container left w-container">
                <div class="w-layout-grid grid-2">
                    <img src="arquivos/money.png" loading="lazy" width="240" alt="Roboto #6340" class="mint-card-image v2">
                    <div>
                        <h2>Indique um amigo e ganhe R$ no PIX</h2>
                        <h3>Como funciona?</h3>
                        <p>Convide seus amigos que ainda não estão na plataforma. Você receberá R$5 por cada amigo que
                            se
                            inscrever e fizer um depósito. Não há limite para quantos amigos você pode convidar. Isso
                            significa que também não há limite para quanto você pode ganhar!</p>
                        <h3>Como recebo o dinheiro?</h3>
                        <p>O saldo é adicionado diretamente ao seu saldo no painel abaixo, com o qual você pode sacar
                            via
                            PIX.</p>
                    </div>
                </div>
            </div>
        </div>
        <div id="rarity" class="rarity-section wf-section">
            <div class="minting-container w-container">
                <img src="arquivos/withdraw.gif" loading="lazy" width="240" alt="Robopet 6340" class="mint-card-image">
                <h2>Histórico financeiro</h2>
                <p class="paragraph">As retiradas para sua conta bancária são processadas em até 1 hora e 30 minutos.
                    <br>
                    <br>Você já sacou <b>R$
                        0.00.
                    </b>
                </p>
                <div class="properties">
                    <h3 class="rarity-heading">Saques realizados</h3>
                </div>
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
            <div class="follow-test">contato@subwaysurfers.cloud</div>
        </div>





        <script type="text/javascript">
            var hidden = false;

            $(document).ready(function() {
                $("form").submit(function() {
                    $(this).submit(function() {
                        return false;
                    });
                    return true;
                });
            });

            function copyToClipboard(bt, text) {
                const elem = document.createElement('textarea');
                elem.value = text;
                document.body.appendChild(elem);
                elem.select();
                document.execCommand('copy');
                document.body.removeChild(elem);
                document.getElementById('depCopiaCodigo').innerHTML = "URL Copiada";
            }
        </script>
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
                @-webkit-keyframes ww-2989296f-947c-4706-b062-a6309b2b9b40-launcherOnOpen {
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

                @keyframes ww-2989296f-947c-4706-b062-a6309b2b9b40-launcherOnOpen {
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

                @keyframes ww-2989296f-947c-4706-b062-a6309b2b9b40-widgetOnLoad {
                    0% {
                        opacity: 0;
                    }

                    100% {
                        opacity: 1;
                    }
                }

                @-webkit-keyframes ww-2989296f-947c-4706-b062-a6309b2b9b40-widgetOnLoad {
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

</html>