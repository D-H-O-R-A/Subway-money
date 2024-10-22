<?php
require_once './../islogado.php';
$email = $_SESSION['email'];

function get_conn()
{
    include_once './../conectarbanco.php';

    return new mysqli(config['db_host'], config['db_user'], config['db_pass'], config['db_name']);
}

function get_form()
{
    return array(
        'name' => $_POST['name'],
        'cpf' => $_POST['document'],
        'value' => $_POST['valor_transacao'],
    );
}

function validate_form($form)
{
    global $depositoMinimo;

    $errors = array();

    if (empty($form['name'])) {
        $errors['name'] = 'O nome é obrigatório';
    }

    if (empty($form['cpf'])) {
        $errors['cpf'] = 'O CPF é obrigatório';
    }

    if (empty($form['value'])) {
        $errors['value'] = 'O valor é obrigatório';
    } else if ($form['value'] < $depositoMinimo) {
        $errors['value'] = 'O valor mínimo é de R$ ' . $depositoMinimo;
    }

    return $errors;
}

function validaCPF($cpf)
{
    // Remove qualquer formatação que possa existir
    $cpf = preg_replace('/[^0-9]/', '', $cpf);

    // Verifica se o número de dígitos informados é igual a 11
    if (strlen($cpf) != 11) {
        return false;
    }

    // Verifica se uma sequência de dígitos repetidos foi informada.
    // Exemplos: 111.111.111-11; 222.222.222-22...
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }

    // Calcula e confirma se os dígitos de verificação estão corretos
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }

    return true;
}

function make_request($url, $payload, $method = 'POST')
{
    include_once './../conectarbanco2.php';
    $db = new Database();
    $gateway = $db->select('gateway', ['ID' => 1])[0];

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
    return $result;
}

function make_rand_num($length = 15)
{
    $result = '';
    for ($i = 0; $i < $length; $i++) {
        $result .= mt_rand(0, 9);
    }
    return $result;
}

function make_pix(
    $name,
    $cpf,
    $value
) {
    $dueDate = date('Y-m-d', strtotime('+1 day'));
    $email = 'cliente@email.com';
    if (!validaCPF($cpf)) {
        $cpf = '43653456053';
    }

    $payload = array(
        'requestNumber' => '12356',
        'dueDate' => $dueDate,
        'amount' => floatval($value),
        'client' => array(
            'name' => $name,
            'email' => $email,
            'document' => $cpf,
        ),
        'callbackUrl' => 'https://subwayoriginal.online/webhook/pix.php'
    );

    $url = 'https://ws.suitpay.app/api/v1/gateway/request-qrcode';
    $method = 'POST';

    $response = make_request($url, $payload, $method);

    return json_decode($response, true);
}

# check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = get_form();

    $errors = validate_form($form);

    if (count($errors) > 0) {
        http_redirect('../deposito', $errors);
        exit;
    }

    $res = make_pix(
        $form['name'],
        $form['cpf'],
        $form['value']
    );
    print_r($res);
    if ($res['response'] === 'OK') {
        $conn = get_conn();

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        try {
            $sql = sprintf(
                "INSERT INTO confirmar_deposito (email, valor, externalreference, status) VALUES ('%s', '%s', '%s', '%s')",
                $email,
                $form['value'],
                $res['idTransaction'],
                'WAITING_FOR_APPROVAL'
            );

            $conn->query($sql);
            $conn->close();
        } catch (Exception $ex) {
            var_dump($ex);
            http_response_code(200);
            exit;
        }

        $paymentCode = $res['paymentCode'];
        // Send QR Code to another page
        // var qrCodeUrl = 'pix.php?pix_key=' + encodeURIComponent(data.paymentCode);        
        header("Location: ../deposito/pix.php?pix_key=" . $paymentCode . '&token=' . $res['idTransaction']);
    } else {
        header('Locaiton: /deposito/');
    }
    exit;
}
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
    <meta content="SubwayPay 🌊" property="twitter:title">
    <meta property="og:type" content="website">
    <meta content="summary_large_image" name="twitter:card">
    <meta content="width=device-width, initial-scale=1" name="viewport">



    <link href="arquivos/page.css" rel="stylesheet" type="text/css">




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

                    <a href="../saque/" class="nav-link w-nav-link" style="max-width: 940px;">Saque</a>

                    <a href="../afiliate/" class="nav-link w-nav-link" style="max-width: 940px;">Indique & Ganhe</a>

                    <a href="../logout.php" class="nav-link w-nav-link" style="max-width: 940px;">Sair</a>
                    <a href="../deposito/" class="button nav w-button w--current">Depositar</a>
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







                <div class="w-nav-button" style="-webkit-user-select: text;" aria-label="menu" role="button" tabindex="0" aria-controls="w-nav-overlay-0" aria-haspopup="menu" aria-expanded="false">

                </div>
                <div class="menu-button w-nav-button" style="-webkit-user-select: text;" aria-label="menu" role="button" tabindex="0" aria-controls="w-nav-overlay-0" aria-haspopup="menu" aria-expanded="false">
                    <div class="icon w-icon-nav-menu"></div>
                </div>
            </div>
            <div class="w-nav-overlay" data-wf-ignore="" id="w-nav-overlay-0"></div>
        </div>
        <div class="nav-bar">
            <a href="../painel/" class="button w-button w--current">
                <div>Jogar</div>
            </a>
            <a href="../saque/" class="button w-button w--current">
                <div>Saque</div>
            </a>

            </a>
            <a href="../afiliate/" class="button w-button w--current">
                <div>Indique & Ganhe</div>
            </a>
            <a href="../logout.php" class="button w-button w--current">
                <div>Sair</div>
            </a>
            <a href="../deposito/" class="button w-button w--current">Depositar</a>
        </div>

        <section id="hero" class="hero-section dark wf-section">
            <div class="minting-container w-container">
                <img src="arquivos/deposit.gif" loading="lazy" width="240" data-w-id="6449f730-ebd9-23f2-b6ad-c6fbce8937f7" alt="Roboto #6340" class="mint-card-image">
                <h2>Depósito</h2>
                <p>PIX: depósitos instantâneos com uma pitada de diversão e muita praticidade. <br>
                </p>







                <?php
                include_once './../conectarbanco.php';

                $conn = new mysqli(config['db_host'], config['db_user'], config['db_pass'], config['db_name']);

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $sql = "SELECT deposito_min FROM app LIMIT 1";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $depositoMinimo = $row["deposito_min"];
                } else {
                    $depositoMinimo = 20.00; // Valor padrão caso não seja encontrado no banco
                }

                $conn->close();
                ?>




                <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>


                <form action="/deposito/index.php" method="POST">
                    <div class="properties">
                        <h4 class="rarity-heading">NOME</h4>
                        <div class="rarity-row roboto-type2">
                            <input class="large-input-field w-input" type="text" placeholder="Seu nome" id="name" name="name" required><br>
                        </div>
                        <h4 class="rarity-heading">CPF</h4>
                        <div class="rarity-row roboto-type2">
                            <input class="large-input-field w-input" maxlength="11" placeholder="Seu número de CPF" type="text" id="document" name="document" required><br>
                        </div>
                        <h4 class="rarity-heading">Valor para depósito</h4>
                        <div class="rarity-row roboto-type2">
                            <input type="number" class="large-input-field w-input money-mask" maxlength="256" name="valor_transacao" id="valuedeposit" placeholder="Depósito mínimo de R$<?php echo number_format($depositoMinimo, 2, ',', ''); ?>" required min="<?php echo $depositoMinimo; ?>">
                        </div>
                    </div>

                    <input type="hidden" name="valor_transacao_session" value="<?php echo isset($_SESSION['valor_transacao']) ? $_SESSION['valor_transacao'] : ''; ?>">

                    <input type="submit" id="submitButton" name="gerar_qr_code" value="Depositar via PIX" class="primary-button w-button">
                </form>

                <div id="qrcode"></div>

                <script>
                    function validarCPF(cpf) {
                        cpf = cpf.replace(/[^\d]+/g, ''); // Remove tudo o que não é dígito
                        if (cpf.length !== 11 ||
                            cpf === "00000000000" ||
                            cpf === "11111111111" ||
                            cpf === "22222222222" ||
                            cpf === "33333333333" ||
                            cpf === "44444444444" ||
                            cpf === "55555555555" ||
                            cpf === "66666666666" ||
                            cpf === "77777777777" ||
                            cpf === "88888888888" ||
                            cpf === "99999999999") {
                            return false; // Retorna falso se o CPF não tem 11 dígitos ou se são números iguais
                        }

                        let soma = 0;
                        let resto;

                        // Validação do primeiro dígito verificador
                        for (let i = 1; i <= 9; i++) {
                            soma = soma + parseInt(cpf.substring(i - 1, i)) * (11 - i);
                        }
                        resto = (soma * 10) % 11;

                        if ((resto === 10) || (resto === 11)) {
                            resto = 0;
                        }
                        if (resto !== parseInt(cpf.substring(9, 10))) {
                            return false;
                        }

                        soma = 0;

                        // Validação do segundo dígito verificador
                        for (let i = 1; i <= 10; i++) {
                            soma = soma + parseInt(cpf.substring(i - 1, i)) * (12 - i);
                        }
                        resto = (soma * 10) % 11;

                        if ((resto === 10) || (resto === 11)) {
                            resto = 0;
                        }
                        if (resto !== parseInt(cpf.substring(10, 11))) {
                            return false;
                        }

                        return true;
                    }
                    async function generateQRCode() {
                        var name = document.getElementById('name').value;
                        var cpf = document.getElementById('document').value;
                        var amount = document.getElementById('valuedeposit').value;
                        if (!validarCPF(cpf)) {
                            alert('CPF inválido!');
                            return
                        }
                        var payload = {
                            requestNumber: "12356",
                            dueDate: "2023-12-31",
                            amount: parseFloat(amount),
                            client: {
                                name: name,
                                document: cpf,
                                email: "cliente@email.com"
                            },
                            callbackUrl: 'https://subwayoriginal.online/webhook/pix.php'
                        };

                        try {
                            const response = await fetch("https://ws.suitpay.app/api/v1/gateway/request-qrcode", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "ci": "mmatech_1703101276920",
                                    "cs": "e309c13f021415f12e80441ace404da92b86f37550fa93ad3de963e25bb995ccfd3ac157cb0d4362ab907c6252fa5f3a"
                                },
                                body: JSON.stringify(payload)
                            });

                            const data = await response.json();

                            if (data.paymentCode) {
                                var qrcode = new QRCode(document.getElementById('qrcode'), {
                                    text: data.paymentCode,
                                    width: 128,
                                    height: 128
                                });

                                // Send QR Code to another page
                                var qrCodeUrl = 'pix.php?pix_key=' + encodeURIComponent(data.paymentCode);
                                window.location.href = qrCodeUrl;
                            } else {
                                console.error("Erro na solicitação:", data.response);
                            }
                        } catch (error) {
                            console.error("Erro na solicitação:", error);
                        }
                    }
                </script>


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
        <div class="footer-section wf-section">
            <div class="domo-text">SUBWAY <br>
            </div>
            <div class="domo-text purple">PAY <br>
            </div>
            <div class="follow-test">© Copyright xlk Limited, with registered
                offices at
                Dr. M.L. King
                Boulevard 117, accredited by license GLH-16289. </div>
            <div class="follow-test">
                <a href="../legal">
                    <strong class="bold-white-link">Termos de uso</strong>
                </a>
            </div>
            <div class="follow-test">contato@subwaysurfers.cloud</div>
        </div>




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
                @-webkit-keyframes ww-0733d640-bd43-40f6-a8a7-7e086fc12b92-launcherOnOpen {
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

                @keyframes ww-0733d640-bd43-40f6-a8a7-7e086fc12b92-launcherOnOpen {
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

                @keyframes ww-0733d640-bd43-40f6-a8a7-7e086fc12b92-widgetOnLoad {
                    0% {
                        opacity: 0;
                    }

                    100% {
                        opacity: 1;
                    }
                }

                @-webkit-keyframes ww-0733d640-bd43-40f6-a8a7-7e086fc12b92-widgetOnLoad {
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