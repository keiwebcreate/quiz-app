<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// 管理画面
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    // 管理画面トップページ
    Route::get('/top', [CategoryController::class, 'index'])->name('top');

    // カテゴリー管理
    Route::prefix('categories')->name('categories.')->group(function () {
        // カテゴリー新規登録
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        // カテゴリー新規登録処理
        Route::post('/store', [CategoryController::class, 'store'])->name('store');
        // カテゴリー詳細画面
        Route::get('/{categoryID}', [CategoryController::class, 'show'])->name('show');
        // カテゴリー編集画面表示
        Route::get('/{categoryID}/edit', [CategoryController::class, 'edit'])->name('edit');
        // カテゴリー更新処理
        Route::put('/{categoryID}/update', [CategoryController::class, 'update'])->name('update');
        // カテゴリー削除処理
        Route::delete('/{categoryID}/destroy', [CategoryController::class, 'destroy'])->name('destroy');

        // クイズ管理
        Route::prefix('/{categoryID}/quizzes')->name('quizzes.')->group(function () {
            // クイズ新規登録画面表示
            Route::get('/create', [QuizController::class, 'create'])->name('create');
            // クイズ新規登録処理
            Route::post('/store', [QuizController::class, 'store'])->name('store');
        });
    });
});
