<?php

use App\Helpers\CoreConstants;
use App\Models\BlogPost;
use Illuminate\Support\Facades\Route;

if (env('APP_ENV') !== 'production') {
    Route::get('command/{cmd}', function ($cmd) {
        try {
            Artisan::call($cmd);

            return response()->json([
                'message' => 'Command successfully executed',
                'payload' => null,
                'status'  => CoreConstants::STATUS_CODE_SUCCESS
            ]);
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    });
}

//log viewer
Route::get('/admin/system-logs', ['\Rap2hpoutre\LaravelLogViewer\LogViewerController', 'index']);


Route::get('/optimize', ['App\Http\Controllers\Admin\AdminController', 'optimize'])->name('optimize');

Route::group(['prefix' => 'admin'], function () {
    Route::get('/{path?}', ['App\Http\Controllers\Admin\AdminController', 'app'])->where('path', '.*')->name('admin.app');
});

Route::get('/sitemap.xml', function () {
    $posts = BlogPost::where('status', 'published')
        ->where(function ($q) {
            $q->whereNull('published_at')
                ->orWhere('published_at', '<=', now()->format('Y-m-d H:i:s'));
        })
        ->orderBy('published_at', 'desc')
        ->get();

    return response()
        ->view('frontend.sitemap', ['posts' => $posts])
        ->header('Content-Type', 'text/xml');
})->name('sitemap');

Route::get('/media/{path}', ['App\Http\Controllers\MediaController', 'show'])
    ->where('path', '.*')
    ->name('media.show');

#region [frontend]

Route::get('/', ['App\Http\Controllers\Frontend\FrontendController', 'index'])->name('frontend');
Route::get('/pixel-tracker', ['App\Http\Controllers\Frontend\FrontendController', 'pixelTracker'])->name('pixel-tracker');
Route::get('/blog/rss.xml', ['App\Http\Controllers\Frontend\BlogController', 'rss'])->name('blog.rss');
Route::get('/blog', ['App\Http\Controllers\Frontend\BlogController', 'index'])->name('blog.index');
Route::get('/blog/{slug}', ['App\Http\Controllers\Frontend\BlogController', 'show'])->name('blog.show');
Route::post('/blog/{slug}/comment', ['App\Http\Controllers\Frontend\BlogController', 'storeComment'])->name('blog.comment');

#endregion
