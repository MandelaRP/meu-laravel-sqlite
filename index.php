<?php

/**
 * Index.php para hospedagem compartilhada simples
 * Este arquivo detecta automaticamente o caminho do Laravel
 */

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Detectar automaticamente o caminho do Laravel
// Se este arquivo está na raiz, o Laravel está na mesma pasta
$laravelPath = __DIR__;

// Verificar se estamos na raiz do Laravel
if (!file_exists($laravelPath . '/vendor/autoload.php')) {
    // Se não encontrou, tenta uma pasta acima (caso esteja em public_html)
    $laravelPath = dirname(__DIR__);
}

// Verificar novamente
if (!file_exists($laravelPath . '/vendor/autoload.php')) {
    die('Erro: Não foi possível encontrar o Laravel. Verifique a estrutura de arquivos.');
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $laravelPath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $laravelPath . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $laravelPath . '/bootstrap/app.php';

$app->handleRequest(Request::capture());

