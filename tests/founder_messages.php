<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\{FounderMessage, MediaAsset, User};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
function check($ok, $message) { if (!$ok) throw new RuntimeException($message); echo "PASS: $message\n"; }
DB::beginTransaction();
try {
    $admin = User::where('is_super_admin', true)->firstOrFail();
    $library = app(App\Services\MediaLibrary::class);
    $controller = app(App\Http\Controllers\FounderMessageController::class);
    $image = '/storage/'.MediaAsset::where('mime_type', 'like', 'image/%')->firstOrFail()->path;
    $tag = 'founder-audit-'.bin2hex(random_bytes(5));
    $request = function ($data) use ($app, $admin) { $r = Request::create('/admin/founder-messages', 'POST', $data); $r->setUserResolver(fn () => $admin); $r->setLaravelSession($app->make('session.store')); return $r; };
    $data = ['title' => 'Message from founder', 'founder_name' => $tag, 'designation' => 'Founder', 'image_path' => $image, 'content' => '<p><strong>Safe founder content</strong></p><script>alert(1)</script>', 'is_active' => true];
    for ($i=1; $i<=3; $i++) { $data['founder_name'] = $tag.' '.$i; $controller->save($request($data), $library); }
    $records = FounderMessage::where('founder_name', 'like', $tag.'%')->orderBy('serial')->get();
    check($records->count() === 3 && !str_contains($records[0]->content, '<script'), 'Create three founder messages and sanitize Summernote HTML');
    $data['title'] = 'Edited founder heading'; $data['founder_name'] = $records[1]->founder_name; $data['image_path'] = ''; $data['is_active'] = false;
    $controller->save($request($data), $library, $records[1]);
    check($records[1]->fresh()->title === $data['title'] && !$records[1]->fresh()->is_active && $records[1]->fresh()->image === $image, 'Edit retains photo and supports hidden status');
    $allIds = FounderMessage::orderBy('serial')->pluck('id')->all();
    $desired = array_reverse($allIds);
    $controller->reorder($request(['ids' => $desired]));
    check(FounderMessage::orderBy('serial')->pluck('id')->all() === $desired, 'Drag/drop order persists');
    try { $controller->reorder($request(['ids' => [$records[0]->id]])); throw new RuntimeException('Incomplete reorder accepted'); } catch (Illuminate\Validation\ValidationException $exception) { check(true, 'Reject stale or incomplete reorder without partial updates'); }
    $response = $controller->show(); $props = (new ReflectionProperty($response, 'props'))->getValue($response);
    check($props['messages']->filter(fn ($record) => str_starts_with($record->founder_name, $tag))->count() === 2, 'Frontend excludes hidden messages');
    check($library->isReferenced($image), 'Founder photo is protected in Media');
    foreach ($records as $record) $controller->destroy($request([]), $record->fresh(), $library);
    check(!FounderMessage::where('founder_name', 'like', $tag.'%')->exists(), 'Delete removes founder messages');
    $guest = new User(['name' => 'Unauthorized user']);
    $r = $request([]); $r->setUserResolver(fn () => $guest);
    try { $controller->index($r); throw new RuntimeException('Unauthorized management accepted'); } catch (Symfony\Component\HttpKernel\Exception\HttpException $exception) { check($exception->getStatusCode() === 403, 'Non-admin management is blocked'); }
} finally { DB::rollBack(); }
echo "PASS: all temporary founder entries rolled back.\n";
