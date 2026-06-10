<?php

function envValue(string $name, string $default): string
{
    $value = getenv($name);

    return $value === false || $value === '' ? $default : $value;
}

function testConnection(string $name, string $dsn, string $user, string $password): array
{
    try {
        $pdo = new PDO($dsn, $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->query('SELECT 1');

        return [
            'success' => true,
            'message' => "Conectado ao {$name} com sucesso!",
        ];
    } catch (PDOException $exception) {
        return [
            'success' => false,
            'message' => "Erro na conexao com {$name}: {$exception->getMessage()}",
        ];
    }
}

$mysql = testConnection(
    'MySQL',
    sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        envValue('MYSQL_HOST', 'mysql-db'),
        envValue('MYSQL_DATABASE', 'usuarios_db')
    ),
    envValue('MYSQL_USER', 'root'),
    envValue('MYSQL_PASSWORD', 'senha_da_nasa')
);

$postgres = testConnection(
    'PostgreSQL',
    sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        envValue('POSTGRES_HOST', 'postgres-db'),
        envValue('POSTGRES_PORT', '5432'),
        envValue('POSTGRES_DB', 'certificados_db')
    ),
    envValue('POSTGRES_USER', 'postgres'),
    envValue('POSTGRES_PASSWORD', 'senha_da_nasa')
);

$checks = [$mysql, $postgres];
$allConnected = $mysql['success'] && $postgres['success'];
http_response_code($allConnected ? 200 : 503);
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Operacao Alfa - Validacao</title>
    <style>
        body {
            max-width: 760px;
            margin: 48px auto;
            padding: 0 20px;
            color: #1f2937;
            font-family: Arial, sans-serif;
        }

        h1 {
            margin-bottom: 8px;
        }

        .subtitle {
            margin-top: 0;
            color: #4b5563;
        }

        .status {
            margin: 16px 0;
            padding: 16px;
            border: 1px solid;
            border-radius: 6px;
        }

        .success {
            color: #166534;
            background: #f0fdf4;
            border-color: #86efac;
        }

        .error {
            color: #991b1b;
            background: #fef2f2;
            border-color: #fca5a5;
        }
    </style>
</head>
<body>
    <h1>Operacao Alfa</h1>
    <p class="subtitle">Validacao de acesso aos bancos de dados</p>

    <?php foreach ($checks as $check): ?>
        <div class="status <?= $check['success'] ? 'success' : 'error' ?>">
            <?= htmlspecialchars($check['message'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endforeach; ?>
</body>
</html>
