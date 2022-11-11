<?php

// CAMINHO DO DIRETÓRIO DE DOWNLOAD NO SERVIDOR
$folder = "download";
$directory = __DIR__ . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;

// ARQUIVOS DO DIRETÓRIO DE DOWNLOAD
$files = is_dir($directory) ? (array) scandir($directory) : [];
$files = array_slice($files, 2);

// ORDENA OS ARQUIVOS POR DATA
usort(
    $files,
    function (string $file1, string $file2) use ($directory): int {
        return filectime($directory . $file1) >= filectime($directory . $file2) ? -1 : 1;
    }
);

// REMOVE OS DIRETÓRIOS E ARQUIVOS PHP DO VETOR
$files = array_values(
    array_filter(
        $files,
        function (string $file) use ($directory): bool {
            $tokens = explode(".php", $file);
            return end($tokens) !== "" || is_dir($directory . $file);
        }
    )
);

// ADICIONA INFORMAÇÕES NO VETOR E IMPRIME
echo json_encode(
    array_map(
        function (string $file) use ($directory, $folder): array {
            return [
                "filename" => $file,
                "directory" => $folder,
                "datetime" => date("d/m/Y - H:i", filectime($directory . $file)),
                "current" => date("Y-m-d", filectime($directory . $file)) === date("Y-m-d")
            ];
        },
        $files
    )
);
