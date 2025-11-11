<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Admin\Images;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class StoreImagesController extends Controller
{
    /**
     * Store banner and logo images
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate([
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB max - apenas JPG, PNG, WEBP
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048', // 2MB max
        ]);

        try {
            $bannerPath = null;
            $logoPath = null;

            // Upload banner
            if ($request->hasFile('banner')) {
                $bannerFile = $request->file('banner');
                $bannerPath = $bannerFile->store('system/banner', 'public');
                
                // Salvar no banco (tabela settings ou system_images)
                $this->saveImageSetting('banner_promocional', $bannerPath);
            }

            // Upload logo
            if ($request->hasFile('logo')) {
                $logoFile = $request->file('logo');
                $logoPath = $logoFile->store('system/logo', 'public');
                
                // Salvar no banco
                $this->saveImageSetting('logo_sidebar', $logoPath);
            }

            return redirect()->route('admin.images.index')
                ->with('success', 'Imagens salvas com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['message' => 'Erro ao salvar imagens: ' . $e->getMessage()]);
        }
    }

    /**
     * Salvar configuração de imagem no banco
     */
    private function saveImageSetting(string $key, string $path): void
    {
        // Se a tabela settings existir, usar ela
        if (DB::getSchemaBuilder()->hasTable('settings')) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $path, 'updated_at' => now()]
            );
        } else {
            // Caso contrário, criar tabela system_images se não existir
            if (!DB::getSchemaBuilder()->hasTable('system_images')) {
                DB::statement('CREATE TABLE IF NOT EXISTS system_images (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    key TEXT UNIQUE NOT NULL,
                    value TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )');
            }
            
            DB::table('system_images')->updateOrInsert(
                ['key' => $key],
                ['value' => $path, 'updated_at' => now()]
            );
        }
    }
}

