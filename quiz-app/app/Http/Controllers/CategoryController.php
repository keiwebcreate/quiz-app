<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\View\View;
use Illuminate\Http\Request;


class CategoryController extends Controller
{
    /**
     * 管理画面トップページとカテゴリー一覧表示
     */
    public function index()
    {
        //カテゴリー一覧を表示
        $categories = Category::get();
        return view('admin.top', ['categories' => $categories]);
    }

    /**
     * カテゴリー新規登録画面表示
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * カテゴリー新規登録処理
     */
    public function store(StoreCategoryRequest $request)
    {
        $category = new Category();
        $category->name = $request->name;
        $category->description = $request->description;
        $category->save();

        return redirect()->route('admin.top');
    }

    /**
     * カテゴリー詳細画面表示 兼 クイズ一覧表示画面
     */
    public function show(Request $request, string $categoryId)
    {
        $category = Category::with('quizzes')->findOrFail($categoryId);
        return view('admin.categories.show', ['category' => $category, 'quizzes' => $category->quizzes]);
    }

    /**
     * カテゴリー編集画面表示
     */
    public function edit(Request $request, string $categoryId)
    {
        $category = Category::findOrFail($categoryId);
        return view('admin.categories.edit', ['category' => $category]);
    }

    /**
     * カテゴリー更新処理
     */
    public function update(UpdateCategoryRequest $request, string $categoryId)
    {
        $category = Category::findOrFail($categoryId);
        $category->name = $request->name;
        $category->description = $request->description;
        $category->save();

        return redirect()->route('admin.categories.show', ['categoryId' => $categoryId]);
    }

    /**
     * カテゴリー削除処理
     */
    public function destroy(string $categoryId)
    {
        $category = Category::findOrFail($categoryId);
        $category->delete();
        return redirect()->route('admin.top');
    }
}
