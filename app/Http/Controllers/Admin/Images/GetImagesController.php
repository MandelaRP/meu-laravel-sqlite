<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Admin\Images;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class GetImagesController extends Controller
{
    /**
     * Get current banner and logo
     */
    public function __invoke(Request $request): Response
    {
        $bannerPath = $this->getImageSetting('banner_promocional');
        $logoPath = $this->getImageSetting('logo_sidebar');

        return Inertia::render('Admin/Images/Index', [
            'bannerUrl' => $bannerPath ? Storage::url($bannerPath) : null,
            'logoUrl' => $logoPath ? Storage::url($logoPath) : null,
        ]);
    }

    /**
     * Buscar configuração de imagem do banco
     */
    private function getImageSetting(string $key): ?string
    {
        if (DB::getSchemaBuilder()->hasTable('settings')) {
            $setting = DB::table('settings')->where('key', $key)->first();
            return $setting?->value;
        }
        
        if (DB::getSchemaBuilder()->hasTable('system_images')) {
            $image = DB::table('system_images')->where('key', $key)->first();
            return $image?->value;
        }
        
        return null;
    }
}

