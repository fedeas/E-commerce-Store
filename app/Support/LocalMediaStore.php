<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocalMediaStore
{
    public function localize(string $value, string $directory): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if ($this->isLocalReference($value)) {
            return $this->toStoragePath($value);
        }

        if (! Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        return $this->download($value, $directory) ?: $value;
    }

    public function publicUrl(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (Str::startsWith($value, ['/media/', 'media/'])) {
            return '/' . ltrim($value, '/');
        }

        if (Str::startsWith($value, ['http://', 'https://', '/'])) {
            return $value;
        }

        return '/media/' . ltrim($value, '/');
    }

    public function isRemote(string $value): bool
    {
        return Str::startsWith(trim($value), ['http://', 'https://']);
    }

    protected function download(string $url, string $directory): ?string
    {
        try {
            $response = Http::timeout(20)
                ->connectTimeout(8)
                ->retry(2, 250)
                ->withHeaders(['User-Agent' => 'MediaImporter/1.0'])
                ->get($url);

            if (! $response->successful() || $response->body() === '') {
                return null;
            }

            $extension = $this->extension($url, $response->header('content-type'));
            $path = trim($directory, '/') . '/' . date('Y/m') . '/' . sha1($url) . '.' . $extension;

            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function extension(string $url, ?string $contentType): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $extension = preg_replace('/[^a-z0-9]/', '', $extension) ?: '';

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'svg', 'mp4', 'webm'], true)) {
            return $extension;
        }

        return match (strtolower(trim((string) explode(';', (string) $contentType)[0]))) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/avif' => 'avif',
            'image/svg+xml' => 'svg',
            'video/mp4' => 'mp4',
            'video/webm' => 'webm',
            default => 'jpg',
        };
    }

    protected function isLocalReference(string $value): bool
    {
        return Str::startsWith($value, ['/media/', 'media/', 'product-images/', 'home-banners/', 'images/', 'uploads/']);
    }

    protected function toStoragePath(string $value): string
    {
        $value = ltrim($value, '/');

        if (Str::startsWith($value, 'media/')) {
            return Str::after($value, 'media/');
        }

        return $value;
    }
}
