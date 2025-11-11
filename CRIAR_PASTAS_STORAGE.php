<?php

/**
 * Script para criar todas as pastas necessárias do storage
 * Execute este arquivo UMA VEZ no servidor após fazer upload
 * 
 * Como usar:
 * 1. Faça upload deste arquivo para a raiz do servidor
 * 2. Acesse: https://auraspay.online/CRIAR_PASTAS_STORAGE.php
 * 3. Após executar, DELETE este arquivo por segurança
 */

$basePath = __DIR__;

$directories = [
    'storage/app/public',
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/testing',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache',
];

echo "<h1>Criando pastas do Storage...</h1>";
echo "<pre>";

foreach ($directories as $dir) {
    $fullPath = $basePath . '/' . $dir;
    
    if (!is_dir($fullPath)) {
        if (mkdir($fullPath, 0755, true)) {
            echo "✅ Criado: {$dir}\n";
        } else {
            echo "❌ Erro ao criar: {$dir}\n";
        }
    } else {
        echo "✓ Já existe: {$dir}\n";
    }
    
    // Tentar definir permissões
    @chmod($fullPath, 0755);
}

echo "\n✅ Concluído!\n";
echo "\n⚠️ IMPORTANTE: Delete este arquivo (CRIAR_PASTAS_STORAGE.php) por segurança!\n";
echo "</pre>";

