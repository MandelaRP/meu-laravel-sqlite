<?php

/**
 * Script para verificar se os assets foram compilados e estão no servidor
 * Execute este arquivo no servidor para diagnosticar problemas de assets
 */

$basePath = __DIR__;

echo "<h1>🔍 Verificação de Assets</h1>";
echo "<pre>";

// Verificar se a pasta build existe
$buildPath = $basePath . '/public/build';
if (!is_dir($buildPath)) {
    echo "❌ ERRO: A pasta public/build/ não existe!\n";
    echo "\n";
    echo "SOLUÇÃO:\n";
    echo "1. Execute 'npm run build' localmente\n";
    echo "2. Faça upload da pasta public/build/ para o servidor\n";
    exit;
}

echo "✅ Pasta public/build/ existe\n\n";

// Verificar manifest.json
$manifestPath = $buildPath . '/manifest.json';
if (!file_exists($manifestPath)) {
    echo "❌ ERRO: O arquivo public/build/manifest.json não existe!\n";
    echo "\n";
    echo "SOLUÇÃO:\n";
    echo "1. Execute 'npm run build' localmente\n";
    echo "2. Faça upload da pasta public/build/ para o servidor\n";
    exit;
}

echo "✅ Arquivo manifest.json existe\n\n";

// Ler e verificar o manifest
$manifest = json_decode(file_get_contents($manifestPath), true);
if (!$manifest) {
    echo "❌ ERRO: O arquivo manifest.json está corrompido!\n";
    exit;
}

echo "✅ Manifest.json é válido\n\n";

// Verificar se há entradas no manifest
if (empty($manifest)) {
    echo "⚠️ AVISO: O manifest.json está vazio\n";
} else {
    echo "✅ Manifest contém " . count($manifest) . " entradas\n\n";
}

// Verificar pasta assets
$assetsPath = $buildPath . '/assets';
if (!is_dir($assetsPath)) {
    echo "❌ ERRO: A pasta public/build/assets/ não existe!\n";
    echo "\n";
    echo "SOLUÇÃO:\n";
    echo "1. Execute 'npm run build' localmente\n";
    echo "2. Faça upload da pasta public/build/ para o servidor\n";
    exit;
}

echo "✅ Pasta public/build/assets/ existe\n\n";

// Contar arquivos JS e CSS
$jsFiles = glob($assetsPath . '/*.js');
$cssFiles = glob($assetsPath . '/*.css');

echo "📊 Arquivos encontrados:\n";
echo "   - JavaScript: " . count($jsFiles) . " arquivo(s)\n";
echo "   - CSS: " . count($cssFiles) . " arquivo(s)\n\n";

if (count($jsFiles) === 0 && count($cssFiles) === 0) {
    echo "❌ ERRO: Nenhum arquivo de asset encontrado!\n";
    echo "\n";
    echo "SOLUÇÃO:\n";
    echo "1. Execute 'npm run build' localmente\n";
    echo "2. Faça upload da pasta public/build/ para o servidor\n";
    exit;
}

// Verificar permissões
$canRead = is_readable($assetsPath);
$canReadManifest = is_readable($manifestPath);

echo "🔒 Permissões:\n";
echo "   - Pasta assets: " . ($canRead ? "✅ Legível" : "❌ Não legível") . "\n";
echo "   - Manifest: " . ($canReadManifest ? "✅ Legível" : "❌ Não legível") . "\n\n";

if (!$canRead || !$canReadManifest) {
    echo "⚠️ AVISO: Problemas de permissão detectados!\n";
    echo "Execute: chmod -R 755 public/build\n";
}

// Verificar URLs dos assets no manifest
echo "🔗 Verificando URLs dos assets no manifest...\n\n";
$foundAssets = 0;
$missingAssets = 0;

foreach ($manifest as $key => $entry) {
    if (isset($entry['file'])) {
        $filePath = $buildPath . '/' . $entry['file'];
        if (file_exists($filePath)) {
            $foundAssets++;
        } else {
            $missingAssets++;
            echo "❌ Arquivo não encontrado: {$entry['file']}\n";
        }
    }
    
    if (isset($entry['css']) && is_array($entry['css'])) {
        foreach ($entry['css'] as $cssFile) {
            $cssPath = $buildPath . '/' . $cssFile;
            if (file_exists($cssPath)) {
                $foundAssets++;
            } else {
                $missingAssets++;
                echo "❌ Arquivo CSS não encontrado: {$cssFile}\n";
            }
        }
    }
}

echo "\n";
echo "✅ Arquivos encontrados: {$foundAssets}\n";
if ($missingAssets > 0) {
    echo "❌ Arquivos faltando: {$missingAssets}\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ VERIFICAÇÃO CONCLUÍDA\n";
echo "═══════════════════════════════════════════════════════════════\n";

if ($missingAssets === 0 && $foundAssets > 0) {
    echo "\n✅ Tudo parece estar correto!\n";
    echo "Se ainda houver problemas, limpe o cache do navegador (Ctrl+Shift+R)\n";
} else {
    echo "\n⚠️ Problemas detectados. Siga as soluções indicadas acima.\n";
}

echo "</pre>";

