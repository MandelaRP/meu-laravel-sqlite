<?php

/**
 * Diagnóstico Completo de Assets
 * Execute este arquivo no servidor para verificar todos os problemas
 */

$basePath = __DIR__;

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Diagnóstico de Assets</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1a1a1a;color:#0f0;}";
echo "h1{color:#0f0;} .ok{color:#0f0;} .erro{color:#f00;} .aviso{color:#ff0;}";
echo "pre{background:#000;padding:10px;border:1px solid #0f0;}</style></head><body>";
echo "<h1>🔍 Diagnóstico Completo de Assets</h1>";

echo "<h2>1. Verificação de Estrutura</h2>";
echo "<pre>";

// Verificar estrutura de pastas
$paths = [
    'Raiz do projeto' => $basePath,
    'Pasta public' => $basePath . '/public',
    'Pasta build' => $basePath . '/public/build',
    'Pasta assets' => $basePath . '/public/build/assets',
    'Manifest' => $basePath . '/public/build/manifest.json',
];

foreach ($paths as $name => $path) {
    $exists = file_exists($path);
    $isDir = is_dir($path);
    $isFile = is_file($path);
    
    $status = $exists ? ($isDir ? "✅ Diretório existe" : ($isFile ? "✅ Arquivo existe" : "⚠️ Existe mas não é arquivo nem diretório")) : "❌ Não existe";
    $color = $exists ? "ok" : "erro";
    
    echo "<span class='{$color}'>{$status}</span>: {$name}\n";
    echo "   Caminho: {$path}\n";
    
    if ($exists && $isDir) {
        $files = glob($path . '/*');
        echo "   Arquivos/Pastas: " . count($files) . "\n";
    }
    echo "\n";
}

echo "</pre>";

echo "<h2>2. Verificação de Arquivos de Build</h2>";
echo "<pre>";

$buildPath = $basePath . '/public/build';
$assetsPath = $buildPath . '/assets';

if (is_dir($assetsPath)) {
    $jsFiles = glob($assetsPath . '/*.js');
    $cssFiles = glob($assetsPath . '/*.css');
    
    echo "Arquivos JavaScript encontrados: " . count($jsFiles) . "\n";
    foreach ($jsFiles as $file) {
        $name = basename($file);
        $size = filesize($file);
        echo "  ✅ {$name} (" . number_format($size / 1024, 2) . " KB)\n";
    }
    
    echo "\nArquivos CSS encontrados: " . count($cssFiles) . "\n";
    foreach ($cssFiles as $file) {
        $name = basename($file);
        $size = filesize($file);
        echo "  ✅ {$name} (" . number_format($size / 1024, 2) . " KB)\n";
    }
} else {
    echo "<span class='erro'>❌ Pasta assets não existe!</span>\n";
}

echo "</pre>";

echo "<h2>3. Verificação do Manifest</h2>";
echo "<pre>";

$manifestPath = $buildPath . '/manifest.json';
if (file_exists($manifestPath)) {
    $manifest = json_decode(file_get_contents($manifestPath), true);
    if ($manifest) {
        echo "✅ Manifest válido\n";
        echo "Entradas no manifest: " . count($manifest) . "\n\n";
        
        // Procurar pela entrada do app.ts
        $appEntry = null;
        foreach ($manifest as $key => $entry) {
            if (strpos($key, 'app.ts') !== false || strpos($key, 'resources/js/app') !== false) {
                $appEntry = $entry;
                break;
            }
        }
        
        if (!$appEntry) {
            // Tentar encontrar qualquer entrada
            $appEntry = reset($manifest);
        }
        
        if ($appEntry) {
            echo "Entrada principal encontrada:\n";
            echo "  Arquivo JS: " . ($appEntry['file'] ?? 'N/A') . "\n";
            if (isset($appEntry['css'])) {
                echo "  Arquivos CSS:\n";
                foreach ($appEntry['css'] as $css) {
                    echo "    - {$css}\n";
                }
            }
            
            // Verificar se os arquivos existem
            echo "\nVerificando existência dos arquivos:\n";
            if (isset($appEntry['file'])) {
                $jsFile = $buildPath . '/' . $appEntry['file'];
                if (file_exists($jsFile)) {
                    echo "  ✅ JS existe: {$appEntry['file']}\n";
                } else {
                    echo "  ❌ JS não existe: {$appEntry['file']}\n";
                    echo "     Procurado em: {$jsFile}\n";
                }
            }
            
            if (isset($appEntry['css'])) {
                foreach ($appEntry['css'] as $css) {
                    $cssFile = $buildPath . '/' . $css;
                    if (file_exists($cssFile)) {
                        echo "  ✅ CSS existe: {$css}\n";
                    } else {
                        echo "  ❌ CSS não existe: {$css}\n";
                        echo "     Procurado em: {$cssFile}\n";
                    }
                }
            }
        }
    } else {
        echo "<span class='erro'>❌ Manifest inválido ou corrompido</span>\n";
    }
} else {
    echo "<span class='erro'>❌ Manifest não existe!</span>\n";
    echo "Caminho esperado: {$manifestPath}\n";
}

echo "</pre>";

echo "<h2>4. Teste de URLs</h2>";
echo "<pre>";

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
$testUrls = [
    '/build/manifest.json',
    '/build/assets/app-S3kx2b0d.js',
    '/build/assets/app-tbkJrANC.css',
];

echo "URL Base: {$baseUrl}\n\n";

foreach ($testUrls as $url) {
    $fullUrl = $baseUrl . $url;
    $localPath = $basePath . '/public' . $url;
    
    echo "URL: {$fullUrl}\n";
    echo "Caminho local: {$localPath}\n";
    echo "Existe localmente: " . (file_exists($localPath) ? "✅ Sim" : "❌ Não") . "\n";
    
    // Tentar fazer uma requisição HTTP
    $ch = curl_init($fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Status HTTP: ";
    if ($httpCode == 200) {
        echo "<span class='ok'>✅ 200 OK</span>\n";
    } elseif ($httpCode == 404) {
        echo "<span class='erro'>❌ 404 Not Found</span>\n";
    } else {
        echo "<span class='aviso'>⚠️ {$httpCode}</span>\n";
    }
    echo "\n";
}

echo "</pre>";

echo "<h2>5. Verificação de Permissões</h2>";
echo "<pre>";

$checkPaths = [
    'public' => $basePath . '/public',
    'build' => $buildPath,
    'assets' => $assetsPath,
];

foreach ($checkPaths as $name => $path) {
    if (file_exists($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $readable = is_readable($path);
        $writable = is_writable($path);
        
        echo "{$name}:\n";
        echo "  Permissões: {$perms}\n";
        echo "  Legível: " . ($readable ? "✅ Sim" : "❌ Não") . "\n";
        echo "  Gravável: " . ($writable ? "✅ Sim" : "❌ Não") . "\n";
        
        if (!$readable) {
            echo "  <span class='aviso'>⚠️ Execute: chmod -R 755 {$path}</span>\n";
        }
        echo "\n";
    }
}

echo "</pre>";

echo "<h2>6. Soluções Recomendadas</h2>";
echo "<pre>";

$hasBuild = is_dir($buildPath);
$hasAssets = is_dir($assetsPath);
$hasManifest = file_exists($manifestPath);

if (!$hasBuild || !$hasAssets || !$hasManifest) {
    echo "<span class='erro'>❌ PROBLEMA DETECTADO: Assets não foram compilados ou enviados!</span>\n\n";
    echo "SOLUÇÃO:\n";
    echo "1. No seu computador, execute:\n";
    echo "   npm install\n";
    echo "   npm run build\n\n";
    echo "2. Faça upload de TODA a pasta public/build/ para o servidor\n";
    echo "3. Verifique se a estrutura ficou assim:\n";
    echo "   /public/build/\n";
    echo "   /public/build/assets/\n";
    echo "   /public/build/manifest.json\n";
} else {
    echo "<span class='ok'>✅ Estrutura de assets parece correta!</span>\n\n";
    echo "Se ainda houver problemas:\n";
    echo "1. Verifique o .htaccess na raiz\n";
    echo "2. Verifique se o index.php está servindo assets corretamente\n";
    echo "3. Limpe o cache do navegador (Ctrl+Shift+R)\n";
    echo "4. Verifique se há erros no console do navegador\n";
}

echo "</pre>";

echo "<h2>7. Informações do Servidor</h2>";
echo "<pre>";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "Script Filename: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "\n";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "HTTP Host: " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n";
echo "</pre>";

echo "<p><strong>⚠️ IMPORTANTE: Delete este arquivo após o diagnóstico por segurança!</strong></p>";
echo "</body></html>";

