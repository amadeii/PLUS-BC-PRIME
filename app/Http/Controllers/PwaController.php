<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracaoSuper;

class PwaController extends Controller
{
    public function manifest()
    {
        $config = ConfiguracaoSuper::first();

        $name = ($config && $config->pwa_name)
            ? $config->pwa_name
            : config('app.name', 'WinGestor');

        $short = mb_substr($name, 0, 14, 'UTF-8');

        $icon = ($config && $config->pwa_icon)
            ? url('/logos/' . $config->pwa_icon)
            : url('/logo-sm.png');

        $themeColor = $config->pwa_color ?? '#0369a1';

        $manifest = [
            'name' => $name,
            'short_name' => $short,
            'description' => $name,
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => '#ffffff',
            'theme_color' => $themeColor,
            'icons' => [
                [
                    'src' => $icon,
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => $icon,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
            ],
        ];

        return response()->json($manifest, 200, [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            ->header('Content-Type', 'application/manifest+json')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}