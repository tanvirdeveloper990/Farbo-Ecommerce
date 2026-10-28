<?php

namespace App\Http\Controllers;

use App\Models\CustomerReview;
use App\Services\MediaLibrary;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerReviewController extends Controller
{
    private function authorizeAccess(Request $r): void
    {
        abort_unless($r->user()->hasPermission('reviews.manage'), 403);
    }

    private function removeImage(?string $path, MediaLibrary $library): void
    {
        if ($path) {
            $library->deleteIfUnused($path);
        }
    }

    public function index(Request $r)
    {
        $this->authorizeAccess($r);
        $filters = $r->validate([
            'search' => 'nullable|string|max:200',
            'status' => 'nullable|in:active,inactive',
        ]);
        $records = CustomerReview::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status'] === 'active'))
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('CustomerReviews', ['records' => $records, 'filters' => $filters]);
    }

    public function form(Request $r, ?CustomerReview $review = null)
    {
        $this->authorizeAccess($r);

        return Inertia::render('CustomerReviewForm', ['review' => $review]);
    }

    public function save(Request $r, MediaLibrary $library, ?CustomerReview $review = null)
    {
        $this->authorizeAccess($r);
        $data = $r->validate(['name' => 'required|string|max:150', 'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240', 'image_path' => 'nullable|string|max:2048', 'star' => 'required|integer|min:1|max:5', 'description' => 'required|string|max:3000', 'status' => 'required|boolean', 'remove_image' => 'sometimes|boolean']);
        $review ??= new CustomerReview;
        $old = $review->image;
        $new = null;
        $newlyUploaded = false;
        unset($data['image'], $data['image_path'], $data['remove_image']);
        if ($r->hasFile('image')) {
            $asset = $library->store($r->file('image'), $r->user()->id);
            $new = '/storage/'.$asset->path;
            $data['image'] = $new;
            $newlyUploaded = true;
        } elseif ($r->filled('image_path')) {
            $new = $library->selectedImage($r->input('image_path'));
            $data['image'] = $new;
        } elseif ($r->boolean('remove_image')) {
            $data['image'] = null;
        }
        try {
            $review->fill($data)->save();
        } catch (\Throwable $e) {
            if ($newlyUploaded) {
                $this->removeImage($new, $library);
            }
            throw $e;
        }
        if ($old !== $review->image) {
            $this->removeImage($old, $library);
        }

        return redirect('/admin/customer-reviews')->with('success', 'Customer review saved successfully.');
    }

    public function destroy(Request $r, CustomerReview $review, MediaLibrary $library)
    {
        $this->authorizeAccess($r);
        $image = $review->image;
        $review->delete();
        $this->removeImage($image, $library);

        return redirect('/admin/customer-reviews')->with('success', 'Customer review deleted successfully.');
    }
}
