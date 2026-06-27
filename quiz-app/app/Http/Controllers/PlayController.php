<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class PlayController extends Controller
{
    //プレイヤー画面TOPページ表示
    public function top() {
        $categories = Category::all();

        return view('play.top', [
            'categories' => $categories
        ]);
    }
}
