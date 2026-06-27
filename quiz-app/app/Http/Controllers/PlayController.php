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

    // クイズスタート画面表示
    public function categories(Request $request, string $categoryId) {
        $category = Category::withCount('quizzes')->findOrFail($categoryId);
        return view('play.start', [
            'category' => $category,
            'quizzesCount' => $category->quizzes_count,
        ]);
    }

    // クイズ出題画面
    public function quizzes(Request $request, string $categoryId) {
        // カテゴリーにひもづくクイズと選択肢をすべて取得
        $category = Category::with('quizzes.options')->findOrFail($categoryId);
        //クイズをランダムに選ぶ
        $quizzes = $category->quizzes->toArray();
        shuffle($quizzes);
        $quiz =$quizzes[0];

        return view('play.quizzes', [
            'categoryId' => $categoryId,
            'quiz' => $quiz
        ]);
    }

    public function answer(Request $request, string $categoryId) {
        $quizId = $request->quizId;
        $optionId = $request->optionId;
        $category = Category::with('quizzes.options')->findOrFail($categoryId);
        $quiz = $category->quizzes()->firstWhere('id', $quizId);
        $quizOptions =$quiz->options->toArray();
        $result = $this->isCorrectAnswer($optionId, $quizOptions);
        return  view('play.answer');
    }

    // プレイヤーの回答が正解か不正解か判定
    private function isCorrectAnswer(array $selectedOptions, array $quizOptions) {
        // クイズの選択肢から正解の選択肢を抽出し、そのIDをすべて取得する。
        $correctOptions = array_filter($quizOptions, function($option) {
            return $option['is_correct'] === 1;
        });
        // プレイヤーが選んだ選択肢の個数と正解の選択肢の個数が一致するか判定す る
            if (count($selectedOptions) !== count($correctOptions) )
            {
                return false;
            }

        // プレイヤーが選んだ選択肢のIDと正解のIDがすべて一致することを判定する
        foreach ($selectedOptions as $selectedOption) {
            if (!in_array($selectedOption, $correctOptions)) {
                return false;
            }
        }
        //正解であることを返す
        return true;
    }
}
