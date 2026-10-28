<?php

namespace App\Http\Controllers;

use App\Models\MediaAsset;
use App\Services\MediaLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class MediaController extends Controller
{
    public function importStorefront(Request $request, \App\Services\StorefrontMediaImporter $importer)
    {
        $this->authorizeAccess($request);
        @set_time_limit(600);
        $report = $importer->run();
        $message = count($report['imported']).' website images are available in Media.';
        if ($report['failed']) return back()->with('error', $message.' '.count($report['failed']).' source images could not be imported; their existing links have been kept.');
        return back()->with('success', $message);
    }
    private function authorizeAccess(Request $request): void
    {
        abort_unless($request->user()->hasPermission('media.manage'), 403);
    }

    public function index(Request $request, MediaLibrary $library)
    {
        $this->authorizeAccess($request);
        $this->registerExistingFiles($library);
        $filters = $request->validate([
            'search' => 'nullable|string|max:200',
            'type' => 'nullable|in:all,images,documents,other',
        ]);
        $referencedUrls = $library->referencedUrls();
        $assets = $this->queryAssets($filters)
            ->latest('id')
            ->paginate(50)
            ->withQueryString()
            ->through(fn (MediaAsset $asset) => $this->assetData($asset, $library, $referencedUrls));

        return Inertia::render('Media', [
            'assets' => $assets,
            'filters' => array_merge(['search' => '', 'type' => 'all'], $filters),
        ]);
    }

    public function library(Request $request, MediaLibrary $library)
    {
        $this->authorizeAccess($request);
        $this->registerExistingFiles($library);
        $filters = $request->validate([
            'search' => 'nullable|string|max:200',
            'page' => 'nullable|integer|min:1',
        ]);
        $referencedUrls = $library->referencedUrls();
        $assets = $this->queryAssets($filters)
            ->latest('id')
            ->paginate(50)
            ->through(fn (MediaAsset $asset) => $this->assetData($asset, $library, $referencedUrls));

        return response()->json($assets);
    }

    public function store(Request $request, MediaLibrary $library)
    {
        $this->authorizeAccess($request);
        $data = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp,svg,pdf,txt,csv,doc,docx,xls,xlsx,ppt,pptx,zip,mp4,webm|max:20480',
        ]);
        $asset = $library->store($data['file'], $request->user()->id);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $this->assetData($asset, $library, $library->referencedUrls()),
            ], 201);
        }

        return back()->with('success', 'File uploaded to the media library.');
    }

    public function destroy(Request $request, MediaAsset $media, MediaLibrary $library)
    {
        $this->authorizeAccess($request);
        $url = Storage::disk($media->disk)->url($media->path);
        if ($library->isReferenced($url)) {
            throw ValidationException::withMessages([
                'media' => 'This file is in use. Replace it everywhere before deleting it.',
            ]);
        }

        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        return back()->with('success', 'File deleted from the media library.');
    }

    private function queryAssets(array $filters)
    {
        return MediaAsset::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('original_name', 'like', '%'.$search.'%')
                        ->orWhere('path', 'like', '%'.$search.'%');
                });
            })
            ->when(($filters['type'] ?? null) === 'images', fn ($query) => $query->where('mime_type', 'like', 'image/%'))
            ->when(($filters['type'] ?? null) === 'documents', fn ($query) => $query->whereIn('mime_type', [
                'application/pdf',
                'text/plain',
                'text/csv',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            ]))
            ->when(($filters['type'] ?? null) === 'other', fn ($query) => $query->where('mime_type', 'not like', 'image/%')->whereNotIn('mime_type', [
                'application/pdf',
                'text/plain',
                'text/csv',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            ]));
    }

    private function assetData(MediaAsset $asset, MediaLibrary $library, array $referencedUrls): array
    {
        $url = Storage::disk($asset->disk)->url($asset->path);

        return [
            'id' => $asset->id,
            'url' => $url,
            'name' => $asset->original_name,
            'mime_type' => $asset->mime_type,
            'size_bytes' => $asset->size_bytes,
            'width' => $asset->width,
            'height' => $asset->height,
            'is_image' => $library->isImage($asset),
            'created_at' => $asset->created_at?->toIso8601String(),
            'in_use' => isset($referencedUrls['/storage/'.$asset->path]),
        ];
    }

    private function registerExistingFiles(MediaLibrary $library): void
    {
        $registeredPaths = array_fill_keys(MediaAsset::pluck('path')->all(), true);
        foreach (Storage::disk('public')->allFiles() as $path) {
            if (! isset($registeredPaths[$path])) {
                $library->register($path, basename($path), null);
            }
        }
    }
}
