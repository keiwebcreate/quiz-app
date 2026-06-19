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
     * カテゴリー詳細画面表示
     */
    public function show(Request $request, string $categoryID)
    {
        $category = Category::findOrFail($categoryID);
        return view('admin.categories.show', ['category' => $category]);
    }

    /**
     * カテゴリー編集画面表示
     */
    public function edit(Request $request, string $categoryID)
    {
        $category = Category::findOrFail($categoryID);
        return view('admin.categories.edit', ['category' => $category]);
    }

    /**
     * カテゴリー更新処理
     */
    public function update(UpdateCategoryRequest $request, string $categoryID)
    {
        $category = Category::findOrFail($categoryID);
        $category->name = $request->name;
        $category->description = $request->description;
        $category->save();

        return redirect()->route('admin.categories.show', ['categoryID' => $categoryID]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        //
    }
}
