<?php

/**
 * Versão do index.php adaptada para hospedagem compartilhada
 * 
 * Use este arquivo se o Laravel estiver em uma pasta diferente do public_html
 * 
 * INSTRUÇÕES:
 * 1. Copie este arquivo para public_html/index.php
 * 2. Ajuste o caminho abaixo para apontar para a raiz do Laravel
 * 3. Exemplo: Se Laravel está em /home/usuario/app/ e public_html em /home/usuario/public_html/
 *    Defina: $laravelPath = '/home/usuario/app';
 */

declare(strict_types = 1);

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// ============================================
// CONFIGURAÇÃO: Ajuste este caminho!
// ============================================
// Caminho absoluto para a raiz do Laravel (onde está a pasta app/, vendor/, etc)
$laravelPath = dirname(__DIR__); // Padrão: uma pasta acima do public

// Se Laravel está em outra localização, descomente e ajuste:
// $laravelPath = '/home/usuario/app';
// ============================================

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

