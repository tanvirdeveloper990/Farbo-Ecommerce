<?php

namespace App\Services;

use App\Models\Banner;
use App\Models\Category;
use App\Models\CustomerReview;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\SiteSetting;
use DOMDocument;
use DOMElement;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class MediaLibrary
{
    private const MAX_IMAGE_DIMENSION = 2560;

    public function store(UploadedFile $file, int $userId): MediaAsset
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $path = match ($extension) {
            'svg' => $this->storeSvg($file),
            'jpg', 'jpeg', 'png', 'gif', 'webp' => $this->storeRaster($file),
            default => $this->storeOriginal($file),
        };

        try {
            return $this->register($path, $file->getClientOriginalName(), $userId);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }
    }

    public function register(string $path, string $originalName, ?int $userId = null): MediaAsset
    {
        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            throw new RuntimeException('The stored media file could not be found.');
        }

        $absolutePath = $disk->path($path);
        $size = $disk->size($path);
        $mime = $disk->mimeType($path) ?: 'application/octet-stream';
        $dimensions = @getimagesize($absolutePath);

        return MediaAsset::updateOrCreate(
            ['path' => $path],
            [
                'disk' => 'public',
                'original_name' => Str::limit($originalName, 255, ''),
                'mime_type' => $mime,
                'size_bytes' => $size,
                'width' => $dimensions[0] ?? null,
                'height' => $dimensions[1] ?? null,
                'created_by' => $userId,
            ],
        );
    }

    public function deleteIfUnused(string $url): bool
    {
        $path = $this->storagePath($url);
        if (! $path || $this->isReferenced($url)) {
            return false;
        }

        Storage::disk('public')->delete($path);
        MediaAsset::where('path', $path)->delete();

        return true;
    }

    public function urlFor(?string $url, ?UploadedFile $file = null): ?string
    {
        if ($file) {
            return null;
        }

        return $url ?: null;
    }

    public function isImage(MediaAsset $asset): bool
    {
        return str_starts_with($asset->mime_type, 'image/');
    }

    public function selectedImage(string $url): string
    {
        $path = $this->storagePath($url);
        $asset = $path ? MediaAsset::where('disk', 'public')->where('path', $path)->first() : null;
        if (! $asset || ! $this->isImage($asset)) {
            throw ValidationException::withMessages([
                'media' => 'Choose a valid image from the media library.',
            ]);
        }

        return Storage::disk($asset->disk)->url($asset->path);
    }

    public function storagePath(string $url): ?string
    {
        // Recognize legacy local URLs after deployment so media references stay protected.
        $host = parse_url($url, PHP_URL_HOST);
        if (in_array($host, ['127.0.0.1', 'localhost', '::1'], true)) {
            $url = parse_url($url, PHP_URL_PATH) ?: $url;
        }
        $prefix = rtrim(Storage::disk('public')->url(''), '/').'/';
        if (str_starts_with($url, $prefix)) {
            $path = substr($url, strlen($prefix));
        } elseif (str_starts_with($url, '/storage/')) {
            $path = substr($url, strlen('/storage/'));
        } else {
            return null;
        }

        return $path !== '' && ! str_contains($path, '..') ? $path : null;
    }

    public function isReferenced(string $url): bool
    {
        return isset($this->referencedUrls()[$this->referenceKey($url)]);
    }

    public function referencedUrls(): array
    {
        $urls = [];
        foreach ([
            [Product::class, 'featured_image_one'],
            [Product::class, 'featured_image_two'],
            [ProductImage::class, 'path'],
            [ProductVariant::class, 'image_path'],
            [Category::class, 'image'],
            [Banner::class, 'image'],
            [CustomerReview::class, 'image'],
            [\App\Models\FounderMessage::class, 'image'],
        ] as [$model, $column]) {
            array_push($urls, ...$model::whereNotNull($column)->distinct()->pluck($column)->all());
        }
        $settings = SiteSetting::current();
        foreach (['header_logo', 'footer_logo', 'favicon', 'why_image', 'footer_background', 'product_video_poster'] as $key) {
            if (! empty($settings[$key])) {
                $urls[] = $settings[$key];
            }
        }

        return array_fill_keys(array_map(fn (string $url) => $this->referenceKey($url), $urls), true);
    }

    private function referenceKey(string $url): string
    {
        $path = $this->storagePath($url);

        return $path ? '/storage/'.$path : $url;
    }

    private function storeOriginal(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $safeExtension = preg_match('/^[a-z0-9]{1,10}$/', $extension) ? $extension : 'bin';
        $path = $file->storeAs('media/files', Str::uuid().'.'.$safeExtension, 'public');
        if (! $path) {
            throw new RuntimeException('The uploaded file could not be saved.');
        }

        return $path;
    }

    private function storeSvg(UploadedFile $file): string
    {
        $source = file_get_contents($file->getRealPath());
        if ($source === false) {
            throw new RuntimeException('The uploaded SVG could not be read.');
        }

        $safeSvg = $this->sanitizeSvg($source);
        $path = 'media/images/'.Str::uuid().'.svg';
        if (! Storage::disk('public')->put($path, $safeSvg)) {
            throw new RuntimeException('The sanitized SVG could not be saved.');
        }

        return $path;
    }

    private function sanitizeSvg(string $source): string
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $loaded = $document->loadXML($source, LIBXML_NONET | LIBXML_NOBLANKS);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        if (! $loaded || ! $document->documentElement || strtolower($document->documentElement->localName) !== 'svg') {
            throw new RuntimeException('The uploaded SVG is not valid SVG markup.');
        }

        $allowedElements = ['svg', 'g', 'path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon', 'defs', 'lineargradient', 'radialgradient', 'stop', 'clippath', 'mask', 'pattern', 'title', 'desc'];
        $allowedAttributes = ['xmlns', 'viewbox', 'width', 'height', 'x', 'y', 'x1', 'y1', 'x2', 'y2', 'cx', 'cy', 'r', 'rx', 'ry', 'd', 'points', 'fill', 'fill-rule', 'fill-opacity', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'stroke-dasharray', 'stroke-dashoffset', 'stroke-opacity', 'opacity', 'transform', 'preserveaspectratio', 'offset', 'stop-color', 'stop-opacity', 'id', 'clip-path', 'mask', 'patternunits', 'gradientunits', 'gradienttransform'];

        $walk = function (DOMElement $element) use (&$walk, $allowedElements, $allowedAttributes): void {
            foreach (iterator_to_array($element->childNodes) as $child) {
                if ($child instanceof DOMElement) {
                    if (! in_array(strtolower($child->localName), $allowedElements, true)) {
                        $element->removeChild($child);

                        continue;
                    }
                    $walk($child);
                }
            }

            foreach (iterator_to_array($element->attributes) as $attribute) {
                $name = strtolower($attribute->localName);
                $value = trim($attribute->value);
                if (! in_array($name, $allowedAttributes, true) ||
                    str_starts_with($name, 'on') ||
                    preg_match('/(?:javascript:|data:|https?:|\/\/|expression\s*\()/i', $value) ||
                    (str_contains(strtolower($name), 'url') && ! preg_match('/^url\(#[a-z0-9_.:-]+\)$/i', $value))) {
                    $element->removeAttributeNode($attribute);
                }
            }
        };
        $walk($document->documentElement);

        return $document->saveXML($document->documentElement) ?: throw new RuntimeException('The SVG could not be sanitized.');
    }

    private function storeRaster(UploadedFile $file): string
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            throw new RuntimeException('Image optimization requires PHP GD with WebP support.');
        }

        $imageInfo = @getimagesize($file->getRealPath());
        if (! $imageInfo || $imageInfo[0] < 1 || $imageInfo[1] < 1 ||
            $imageInfo[0] > 12000 || $imageInfo[1] > 12000 ||
            $imageInfo[0] * $imageInfo[1] > 40000000) {
            throw new RuntimeException('The image dimensions exceed the supported limit.');
        }

        $contents = file_get_contents($file->getRealPath());
        $image = $contents === false ? false : @imagecreatefromstring($contents);
        if (! $image) {
            throw new RuntimeException('The uploaded image could not be decoded.');
        }

        $width = imagesx($image);
        $height = imagesy($image);
        if ($width < 1 || $height < 1 || $width * $height > 40000000) {
            imagedestroy($image);
            throw new RuntimeException('The image dimensions exceed the supported limit.');
        }

        $scale = min(1, self::MAX_IMAGE_DIMENSION / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $optimized = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($optimized, false);
        imagesavealpha($optimized, true);
        $transparent = imagecolorallocatealpha($optimized, 0, 0, 0, 127);
        imagefill($optimized, 0, 0, $transparent);
        imagecopyresampled($optimized, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        $written = imagewebp($optimized, null, 82);
        $optimizedContents = ob_get_clean();
        imagedestroy($optimized);
        imagedestroy($image);
        if (! $written || ! is_string($optimizedContents) || $optimizedContents === '') {
            throw new RuntimeException('The image could not be converted to optimized WebP.');
        }

        $path = 'media/images/'.Str::uuid().'.webp';
        if (! Storage::disk('public')->put($path, $optimizedContents)) {
            throw new RuntimeException('The optimized image could not be saved.');
        }

        return $path;
    }
}
