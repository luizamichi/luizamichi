<?php

// INICIA UMA SESSÃO, CASO PRECISE SALVAR QUE O USUÁRIO ENVIOU UMA MENSAGEM, PARA BLOQUEAR QUE ENVIE NOVAMENTE
ini_set('session.cookie_secure', 1);
if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

// MÉTODO DE REQUISIÇÃO RECEBIDO E DADO DA SESSÃO
$requestMethod = isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' ? 'POST' : 'GET';
$sessionForm = isset($_SESSION['MESSAGE_SENT']) && $_SESSION['MESSAGE_SENT'] === true ?: false;

// EVITA QUE O USUÁRIO ENVIE MAIS DE UMA MENSAGEM POR SESSÃO
if ($requestMethod === 'POST' && $sessionForm === false) {
	// LIMPA OS TEXTOS ENVIADOS
	$emailForm = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
	$nameForm = filter_input(INPUT_POST, 'name', FILTER_DEFAULT);
	$messageForm = filter_input(INPUT_POST, 'message', FILTER_DEFAULT);

	// REMOVE ESPAÇOS DESNECESSÁRIOS E CARACTERES PERIGOSOS (FECHAMENTO DE COMENTÁRIO EXTENSO)
	$emailForm = trim(str_replace('*/', '', $emailForm));
	$nameForm = trim(str_replace('*/', '', $nameForm));
	$messageForm = trim(str_replace('*/', '', $messageForm));

	// ESCREVE NO ARQUIVO DE MENSAGENS DOS USUÁRIOS
	$contentMessage = PHP_EOL . chr(9) . '/* DATA: ' . date('d/m/Y H:i:s') .
		PHP_EOL . chr(9) . ' * E-MAIL: ' . $emailForm .
		PHP_EOL . chr(9) . ' * NOME: ' . $nameForm .
		PHP_EOL . chr(9) . ' * MENSAGEM: ' . str_replace(chr(10), chr(10) . chr(9) . '   ', $messageForm) .
		PHP_EOL . chr(9) . ' */' . PHP_EOL;
	$fileDescriptor = fopen('message.php', 'a');
	fwrite($fileDescriptor, $contentMessage);
	fclose($fileDescriptor);

	// ENVIA UM E-MAIL COM A MENSAGEM
	mail('luiz.amichi@solus.inf.br', '[Solus Computação] Nova mensagem recebida', nl2br($contentMessage));

	// SALVA NA SESSÃO QUE O USUÁRIO JÁ ENVIOU MENSAGEM
	$_SESSION['MESSAGE_SENT'] = true;

	// RECARREGA A PÁGINA PARA EVITAR QUE O USUÁRIO APERTE F5 E CARREGUE O MODO POST NOVAMENTE
	header('Location: ' . $_SERVER['PHP_SELF'] . '#section-message');
}

// CAMINHOS DOS DIRETÓRIOS NO SERVIDOR
$homeDirectory = __DIR__ . DIRECTORY_SEPARATOR;
$downloadDirectory = $homeDirectory . 'download' . DIRECTORY_SEPARATOR;
$fontDirectory = $homeDirectory . 'font' . DIRECTORY_SEPARATOR;
$imageDirectory = $homeDirectory . 'image' . DIRECTORY_SEPARATOR;
$scriptDirectory = $homeDirectory . 'script' . DIRECTORY_SEPARATOR;
$stylesheetDirectory = $homeDirectory . 'stylesheet' . DIRECTORY_SEPARATOR;

// CAMINHOS DOS DIRETÓRIOS NA WEB
$homeRoute = '/pk/luiz/';
$homeRoute = '/';
$downloadRoute = $homeRoute . 'download/';
$fontRoute = $homeRoute . 'font/';
$imageRoute = $homeRoute . 'image/';
$scriptRoute = $homeRoute . 'script/';
$stylesheetRoute = $homeRoute . 'stylesheet/';

// ARQUIVOS DO DIRETÓRIO DE DOWNLOAD
$downloadFiles = (array) scandir($downloadDirectory);
$downloadFiles = array_slice($downloadFiles, 2);

// ORDENA OS ARQUIVOS POR DATA
usort($downloadFiles, function (string $file1, string $file2) use ($downloadDirectory): int {
	if (filectime($downloadDirectory . $file1) >= filectime($downloadDirectory . $file2)) {
		return -1;
	}
	return 1;
});

// REMOVE OS ARQUIVOS PHP DO VETOR
$downloadFiles = array_filter($downloadFiles, function (string $file): bool {
	$tokens = explode('.php', $file);
	return end($tokens) !== '';
});

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="UTF-8"/>
	<meta content="pt-br" name="language"/>
	<meta content="nofollow" name="robots"/>
	<meta content="IE=edge" http-equiv="X-UA-Compatible"/>
	<meta content="Luiz Joaquim Aderaldo Amichi" name="author"/>
	<meta content="O Solus Saúde é hoje o sistema de Gestão de Operadoras de Planos de Saúde mais moderno e seguro do mercado brasileiro." name="description"/>
	<meta content="Operadora de saúde, plano de saúde, Solus, web." name="keywords"/>
	<meta content="width=device-width, initial-scale=1.0" name="viewport"/>

	<meta content="O Solus Saúde é hoje o sistema de Gestão de Operadoras de Planos de Saúde mais moderno e seguro do mercado brasileiro." property="og:description"/>
	<meta content="https://luizamichi.com.br/images/card.png" property="og:image"/>
	<meta content="https://luizamichi.com.br/images/card.png" property="og:image:secure_url"/>
	<meta content="pt_BR" property="og:locale"/>
	<meta content="Solus" property="og:site_name"/>
	<meta content="website" property="og:type"/>
	<meta content="Solus - Luiz Amichi" property="og:title"/>
	<meta content="http://download.solus.inf.br:8080/pk/luiz/" property="og:url"/>

	<meta content="summary_large_image" name="twitter:card"/>
	<meta content="O Solus Saúde é hoje o sistema de Gestão de Operadoras de Planos de Saúde mais moderno e seguro do mercado brasileiro." name="twitter:description"/>
	<meta content="luizamichi.com.br" name="twitter:domain"/>
	<meta content="https://luizamichi.com.br/images/card.png" name="twitter:image"/>
	<meta content="Solus - Luiz Amichi" name="twitter:title"/>

	<title>Solus - Luiz Amichi</title>

	<link href="<?= $stylesheetRoute ?>bootstrap.min.css" rel="stylesheet" type="text/css"/>
	<link href="<?= $stylesheetRoute ?>bootstrap-icons.css" rel="stylesheet" type="text/css"/>
	<link href="<?= $imageRoute ?>favicon.ico" rel="icon" type="image/x-icon"/>

	<style>
		/* DEFINE AS CORES (PRIMÁRIA E SECUNDÁRIA) DA SOLUS */
		:root {
			--color-primary: #0053bc;
			--color-secondary: #ce275f;
		}

		/* DEIXA A SELEÇÃO DA COR SECUNDÁRIA DA SOLUS */
		*::selection {
			background-color: var(--color-secondary);
			color: #f8f9fa;
		}
	</style>
</head>

<body class="bg-light">
	<!-- CABEÇALHO -->
	<header class="bg-info py-3">
		<div class="container" id="section-header">
			<div class="text-center">
				<img alt="Solus" class="img-fluid" src="<?= $imageRoute ?>logo.png"/>
			</div>
		</div>
	</header>


	<!-- TEXTO DE INFORMAÇÃO -->
	<div class="container my-5">
		<p class="lead">
			Hoje, temos o sistema de Gestão de Operadoras de Planos de Saúde mais moderno e seguro do mercado brasileiro.
			Desenhado a partir da arquitetura de informação Solus Orbital, a solução teve o seu código escrito de forma integral, assim processa todos os dados a partir de um único núcleo central.
		</p>
		<p class="lead">
			A Solus Saúde é uma solução desenvolvida especialmente para atender uma operadora moderna.
		</p>
	</div>


	<!-- DOWNLOAD -->
	<div class="bg-info" id="section-download">
		<div class="fs-1 text-center text-light">
			<em class="bi bi-download"></em>
			Download
		</div>
	</div>


	<!-- ARQUIVOS DE DOWNLOAD -->
	<div class="container-sm mb-5">
	<?php if (count($downloadFiles) > 0) : ?>
		<div class="justify-content-center row">
		<?php foreach ($downloadFiles as $file) : ?>
			<?php if (!is_dir($downloadDirectory . $file)) : ?>
			<div class="col-md-4 col-sm-6">
				<p class="mb-0 mt-3">
					<a class="fs-4 text-dark text-decoration-none" download="<?= $file ?>" href="<?= $downloadRoute . $file ?>"><?= $file ?></a>
					<?php if (date('Y-m-d', filectime($downloadDirectory . $file)) === date('Y-m-d')) : ?>
					<span class="badge bg-info">NOVO</span>
					<?php endif; ?>
				</p>
				<em class="bi bi-file-earmark-arrow-down"></em>
				<small class="text-muted"><?= date('d/m/Y - H:i', filectime($downloadDirectory . $file)) ?></small>
			</div>
			<?php endif; ?>
		<?php endforeach; ?>
		</div>

	<?php else : ?>
		<p class="text-center text-lead">Não há arquivos disponíveis para download.</p>
	<?php endif; ?>
	</div>


	<!-- MENSAGEM -->
	<div class="bg-info" id="section-message">
		<div class="fs-1 text-center text-light">
			<em class="bi bi-chat-left-dots"></em>
			Mensagem
		</div>
	</div>


	<!-- FORMULÁRIO -->
	<div class="container-sm mb-5">
		<?php if (!$sessionForm) : ?>
		<form action="<?= $homeRoute ?>" id="form-message" method="POST">
			<label for="name" class="form-label">Nome</label>
			<div class="input-group mb-3">
				<span class="input-group-text"><em class="bi bi-person"></em></span>
				<input class="form-control" id="name" maxlength="64" minlength="4" name="name" placeholder="Nome" required="required" type="text"/>
			</div>

			<label for="email" class="form-label">E-mail</label>
			<div class="input-group mb-3">
				<span class="input-group-text"><em class="bi bi-envelope"></em></span>
				<input class="form-control" id="email" maxlength="32" minlength="8" name="email" placeholder="E-mail" type="email"/>
			</div>

			<label for="message" class="form-label">Mensagem</label>
			<div class="input-group mb-3">
				<span class="input-group-text"><em class="bi bi-card-text"></em></span>
				<textarea class="form-control" id="message" maxlength="512" minlength="16" name="message" placeholder="Mensagem" required="required" rows="4"></textarea>
			</div>
			<button class="btn btn-info text-light" id="submit" type="submit">
				<em class="bi bi-check"></em>
				Enviar
			</button>
			<button class="btn btn-dark" id="reset" type="reset">
				<em class="bi bi-eraser"></em>
				Limpar
			</button>

			<div class="text-center">
				<div class="spinner-border text-info" id="loading" style="display: none;">
					<span class="sr-only"></span>
				</div>
			</div>
		</form>

		<?php else : ?>
		<p class="lead mt-5 text-center" id="alert-message">Sua mensagem foi enviada.</p>
		<?php endif; ?>
	</div>


	<!-- CONTATO -->
	<div class="bg-info" id="section-contact">
		<div class="fs-1 text-center text-light">
			<em class="bi bi-journal-bookmark-fill"></em>
			Contato
		</div>
	</div>


	<!-- LINKS DE CONTATO -->
	<div class="container-sm mb-3">
		<div class="justify-content-center row">
			<div class="col-md-3 col-sm-4">
				<div class="mb-0 mt-3 text-center">
					<a class="fs-2 text-primary" data-bs-placement="top" data-bs-toggle="tooltip" href="mailto:luiz.amichi@solus.inf.br" title="luiz.amichi@solus.inf.br">
						<em class="bi bi-envelope-fill"></em>
					</a>
					<p><small>E-mail</small></p>
				</div>
			</div>

			<div class="col-md-3 col-sm-4">
				<div class="mb-0 mt-3 text-center">
					<a class="fs-2 text-dark" data-bs-placement="top" data-bs-toggle="tooltip" href="https://github.com/luizamichi" title="github.com/luizamichi">
						<em class="bi bi-github"></em>
					</a>
					<p><small>GitHub</small></p>
				</div>
			</div>

			<div class="col-md-3 col-sm-4">
				<div class="mb-0 mt-3 text-center">
					<a class="fs-2 text-info" data-bs-placement="top" data-bs-toggle="tooltip" href="skype:luiz.amichi.solus@outlook.com?chat" title="luiz.amichi.solus@outlook.com">
						<em class="bi bi-skype"></em>
					</a>
					<p><small>Skype</small></p>
				</div>
			</div>

			<div class="col-md-3 col-sm-4">
				<div class="mb-0 mt-3 text-center">
					<a class="fs-2 text-dark" data-bs-placement="top" data-bs-toggle="tooltip" href="https://luizamichi.com.br" title="luizamichi.com.br">
						<em class="bi bi-globe2"></em>
					</a>
					<p><small>Web</small></p>
				</div>
			</div>
		</div>
	</div>


	<!-- RODAPÉ -->
	<footer class="bg-info py-3">
		<div class="text-center text-light">Copyright &copy; Solus 2021 - <?= date('Y') ?></div>
	</footer>


	<!-- SCRIPTS -->
	<script src="<?= $scriptRoute ?>jquery.min.js"></script>
	<script src="<?= $scriptRoute ?>popper.min.js"></script>
	<script src="<?= $scriptRoute ?>bootstrap.min.js"></script>

	<script>
	$(document).ready(function() {
		// HABILITA OS TOOLTIPS DOS LINKS DE CONTATO
		$("[data-bs-toggle='tooltip']").tooltip();

		// REALIZA ALGUNS AJUSTES VISUAIS ANTES DE ENVIAR O FORMULÁRIO
		$("#form-message").submit(function() {
			// DESABILITA OS INPUTS DO FORMULÁRIO
			$("#name").attr("readonly", true);
			$("#email").attr("readonly", true);
			$("#message").attr("readonly", true);
			$("#submit").attr("disabled", true);
			$("#reset").attr("disabled", true);

			// HABILITA O SPINNER
			$("#loading").fadeIn(1000);
		});
	});
	</script>
</body>

</html>
