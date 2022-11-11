<?php

// INICIA UMA SESSÃO, CASO PRECISE SALVAR QUE O USUÁRIO ENVIOU UMA MENSAGEM, PARA BLOQUEAR QUE ENVIE NOVAMENTE
ini_set("session.cookie_secure", 1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// MÉTODO DE REQUISIÇÃO RECEBIDO E DADO DA SESSÃO
$requestMethod = isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "POST" ? "POST" : "GET";
$sessionForm = isset($_SESSION["MESSAGE_SENT"]) && $_SESSION["MESSAGE_SENT"] === true ?: false;

// MENSAGEM DE RETORNO PARA MÉTODO POST
$statusMessage = "Você já enviou uma mensagem anteriormente. Agradecemos o seu envio.";

// EVITA QUE O USUÁRIO ENVIE MAIS DE UMA MENSAGEM POR SESSÃO
if ($requestMethod === "POST" && $sessionForm === false) {
    http_response_code(400);

    // LIMPA OS TEXTOS ENVIADOS
    $nameForm = (string) filter_input(INPUT_POST, "name", FILTER_DEFAULT);
    $emailForm = (string) filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
    $messageForm = (string) filter_input(INPUT_POST, "message", FILTER_DEFAULT);

    // VALIDA SE OS CAMPOS ESTÃO OK
    $validation = [];
    !(strlen($nameForm) >= 4 && strlen($nameForm) <= 64)
        && array_push($validation, "o nome precisa ter no mínimo 4 caracteres e no máximo 64 caracteres");
    !(strlen($emailForm) >= 8 && strlen($emailForm) <= 32 && filter_var($emailForm, FILTER_VALIDATE_EMAIL))
        && array_push($validation, "o e-mail precisa ser válido e ter no mínimo 8 caracteres e no máximo 32 caracteres");
    !(strlen($messageForm) >= 16 && strlen($messageForm) <= 512)
        && array_push($validation, "a mensagem precisa ter no mínimo 16 caracteres e no máximo 512 caracteres");

    $statusMessage = "Não foi possível enviar a mensagem, " . implode(", ", $validation) . ".";

    if (empty($validation)) {
        // REMOVE ESPAÇOS DESNECESSÁRIOS E CARACTERES PERIGOSOS (FECHAMENTO DE COMENTÁRIO EXTENSO)
        $nameForm = trim(str_replace("*/", "", $nameForm));
        $emailForm = trim(str_replace("*/", "", $emailForm));
        $messageForm = trim(str_replace("*/", "", $messageForm));

        // ESCREVE NO ARQUIVO DE MENSAGENS DOS USUÁRIOS
        $contentMessage = PHP_EOL . "/* DATA: " . date("d/m/Y H:i:s") .
        PHP_EOL . " * E-MAIL: " . $emailForm .
        PHP_EOL . " * NOME: " . $nameForm .
        PHP_EOL . " * MENSAGEM: " . str_replace(chr(10), chr(10) . "   ", $messageForm) .
        PHP_EOL . " */" . PHP_EOL;
        $fileDescriptor = fopen("messages.php", "a");
        fwrite($fileDescriptor, $contentMessage);
        fclose($fileDescriptor);

        // ENVIA UM E-MAIL COM A MENSAGEM
        mail("luiz.amichi@solus.inf.br", "[Solus Computação] Nova mensagem recebida", nl2br($contentMessage));

        // SALVA NA SESSÃO QUE O USUÁRIO JÁ ENVIOU MENSAGEM
        $_SESSION["MESSAGE_SENT"] = true;

        http_response_code(200);
        $statusMessage = "Agradecemos o seu envio. Sua opinião é muito importante para nós.";
    }
}

// REDIRECIONA PARA A PÁGINA PRINCIPAL (MÉTODO GET)
$requestMethod === "GET" && header("Location: index.html");

// RETORNA O JSON DE RESPOSTA (MÉTODO POST)
exit(
    json_encode(
        [
            "message" => $statusMessage
        ]
    )
);

// ABAIXO SERÃO ESCRITAS AS MENSAGENS DOS USUÁRIOS
