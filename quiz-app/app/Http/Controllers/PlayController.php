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
        // セッションの削除
        session()->forget('resultArray');
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
        // $quizzes = $category->quizzes->toArray();
        // shuffle($quizzes);
        // $quiz =$quizzes[0];

        // セッションに保存されているクイズIDの配列を取得
        $resultArray = session('resultArray');
        // 初回アクセス時はセッションに保存されたクイズIDの配列がないため、クイズIDの配列を作成する
        if (is_null($resultArray)) {
            // クイズIDをすべて抽出する
            $quizIds = $category->quizzes->pluck('id')->toArray();
            // クイズIDの配列をランダムに入れ替える
            shuffle($quizIds);
            $resultArray = [];
            foreach ($quizIds as $quizId) {
                $resultArray[] = [
                    'quizId' => $quizId,
                    'result' => null,
                ];
            }
            // クイズIDの配列をセッションに保存
            session(['resultArray' => $resultArray]);
        }

        // $resultArrayのなかで、resultがnullのもののうち、最初のデータを選ぶ
        $noAnswerResult = collect($resultArray)->filter(function($item) {
            return $item['result'] === null;
        })->first();

        if (!$noAnswerResult) {
            // すべてのクイズに回答済みの場合はリザルト画面にリダイレクトする
            // dd('未回答のクイズはなくなりました');
            return redirect()->route('categories.quizzes.result', ['categoryId' => $categoryId]);
        }

        //クイズIDに紐づくクイズを取得
        $quiz = $category->quizzes->firstWhere('id', $noAnswerResult['quizId'])->toArray();


        return view('play.quizzes', [
            'categoryId' => $categoryId,
            'quiz' => $quiz
        ]);
    }

    public function answer(Request $request, string $categoryId) {
        $quizId = $request->quizId;
        $selectedOptions = $request->optionId == null ? [] : $request->optionId;
        $category = Category::with('quizzes.options')->findOrFail($categoryId);
        $quiz = $category->quizzes()->firstWhere('id', $quizId);
        $quizOptions =$quiz->options->toArray();
        $isCorrectAnswer = $this->isCorrectAnswer($selectedOptions, $quizOptions);

        // セッションからクイズIDと回答情報を取得
        $resultArray = session('resultArray',[]);
        // 回答結果をセッションに保存する
        foreach($resultArray as $index => $result) {
            if($result['quizId'] === (int)$quizId) {
                $resultArray[$index]['result'] = $isCorrectAnswer;
                break;
            }
        }
        // 回答結果をセッションに上書き保存する
        session(['resultArray' => $resultArray]);

        return  view('play.answer', [
            'isCorrectAnswer' => $isCorrectAnswer,
            'quiz' => $quiz->toArray(),
            'quizOptions' => $quizOptions,
            'selectedOptions' => $selectedOptions,
            'categoryId' => $categoryId
        ]);
    }
    // リザルト画面表示
    public function result(Request $request, string $categoryId){

        $resultArray = session('resultArray');

        $questionCount = count($resultArray);
        $correctCount = collect($resultArray)->filter(function($result) {
            return $result['result'] === true;
        })->count();
        // dd($categoryId, $questionCount, $correctCount);
        
        return view('play.result',[
            'categoryId' => $categoryId,
            'questionCount' => $questionCount,
            'correctCount' => $correctCount
        ]);
    }

    // プレイヤーの回答が正解か不正解か判定
    private function isCorrectAnswer(array $selectedOptions, array $quizOptions) {
        // クイズの選択肢から正解の選択肢を抽出し、そのIDをすべて取得する。
        $correctOptions = array_filter($quizOptions, function($option) {
            return $option['is_correct'] === 1;
        });

        //IDの数字だけを抽出する。
        $correctOptionIds = array_map(function ($option) {
            return $option['id'];
        },$correctOptions);

        // プレイヤーが選んだ選択肢の個数と正解の選択肢の個数が一致するか判定す る
            if (count($selectedOptions) !== count($correctOptionIds) )
            {
                return false;
            }



        // プレイヤーが選んだ選択肢のIDと正解のIDがすべて一致することを判定する
        foreach ($selectedOptions as $selectedOption) {
            if (!in_array((int)$selectedOption, $correctOptionIds)) {
                return false;
            }
        }
        //正解であることを返す
        return true;
    }
}
