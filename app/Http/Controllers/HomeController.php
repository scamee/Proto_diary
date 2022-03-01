<?php

namespace App\Http\Controllers;

use App\Facades\Calendar;
use Illuminate\Http\Request;
use App\Models\Diary;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

        return view(
            'home',
            [
                'weeks'         => Calendar::getWeeks(),
                'month'         => Calendar::getMonth(),
                'prev'          => Calendar::getPrev(),
                'next'          => Calendar::getNext(),
            ]
        );
    }

    public function create()
    {
        $user = \Auth::user();
        return view('create', compact('user'));
    }

    //一覧表示
    public function show($date)
    {
        $user = \Auth::user();
        $diary = Diary::where('user_id', $user['id'])->where('diary_date', $date)->first();


        return view(
            'show',
            compact('user', 'diary'),
            [
                'weeks'         => Calendar::getWeeks(),
                'month'         => Calendar::getMonth(),
                'prev'          => Calendar::getPrev(),
                'next'          => Calendar::getNext(),
                'date'          => Calendar::getDay($date)
            ]
        );
    }


    //編集画面
    public function edit($date)
    {
        $user = \Auth::user();

        //消す
        $diary = Diary::select('title', 'health', 'content')->where('user_id', $user['id'])->where('diary_date', $date)->first();

        return view(
            'edit',
            compact('user', 'date', 'diary'),
            [
                'weeks'         => Calendar::getWeeks(),
                'month'         => Calendar::getMonth(),
                'prev'          => Calendar::getPrev(),
                'next'          => Calendar::getNext(),
            ]
        );
    }

    //編集アクション
    public function store(Request $request)
    {
        $data = $request->all();

        $Diary_test = Diary::insertGetId([
            "diary_date" => $data["diary_date"],
            "user_id" => $data["user_id"],
            "title" => $data["title"],
            "health" => $data["select"],
            "content" => $data["content"],
        ]);

        // リダイレクト処理
        return redirect()->route('home');
    }
}
