<?php
/* original class file */

namespace app\Services;

use Carbon\Carbon;
use App\Models\Tag;
use App\Models\Diary;

class CalendarService
{
    /**
     * カレンダーデータを返却する
     *
     * @return array
     */
    public function getWeeks()
    {
        $weeks = [];
        $week = '';

        $dt = new Carbon(self::getYm_firstday());
        $day_of_week = $dt->dayOfWeek;     // 曜日
        $days_in_month = $dt->daysInMonth; // その月の日数


        // 第 1 週目に空のセルを追加
        $week .= str_repeat('<td></td>', $day_of_week);

        for ($day = 1; $day <= $days_in_month; $day++, $day_of_week++) {
            $day = sprintf('%02d', $day);
            $date = self::getYm() . '-' . $day;

            if (self::getNow() === $date) {
                $week .= '<td class="today"><a href="/show/' . $date . '">' . $day;
            } else {
                $week .= '<td><a href="/show/' . $date . '">' . $day;
            }

            //ログインユーザーの記入済み日記一覧
            $completed_my_diaries = in_array($date, self::getDiary());
            //パートナーユーザーの記入済み日記一覧
            $completed_partner_diaries = in_array($date, self::getPartnerDiary());

            if ($completed_my_diaries) {
                $week .= '<span class="my-check">自分</span>';
            }
            if ($completed_partner_diaries) {
                $week .= '<span class="partner-check">相手</span></a></td>';
                //<i class="partner-check fa-regular fa-circle-check">
            } else {
                $week .= '</a></td>';
            }
            // 週の終わり、または月末
            $day = (int)$day;
            if (($day_of_week % 7 === 6) || ($day === $days_in_month)) {
                if ($day === $days_in_month) {
                    $week .= str_repeat('<td></td>', 6 - ($day_of_week % 7));
                }
                $weeks[] = '<tr>' . $week . '</tr>';
                $week = '';
            }
        }
        return $weeks;
    }

    /**
     * month 文字列を返却する
     *
     * @return string
     */
    public function getMonth()
    {
        return Carbon::parse(self::getYm_firstday())->format('Y年n月');
    }

    /**
     * prev 文字列を返却する
     *
     * @return string
     */
    public function getPrev()
    {
        return Carbon::parse(self::getYm_firstday())->subMonthsNoOverflow()->format('Y-m-d');
    }

    /**
     * next 文字列を返却する
     *
     * @return string
     */
    public function getNext()
    {
        return Carbon::parse(self::getYm_firstday())->addMonthNoOverflow()->format('Y-m-d');
    }

    /**
     *　現在日時を返却する
     *
     * @return string
     */
    public function getNow()
    {
        return Carbon::now()->format('Y-m-d');
    }

    /**
     * URL から Y-m フォーマットを返却する
     *
     * @return string
     */
    private static function getYm()
    {
        $currentroute = \Route::currentRouteName();
        $setroutes = self::set_routes();

        foreach ($setroutes as $setroute) {
            if ($currentroute === $setroute) {
                $url = $_SERVER['REQUEST_URI'];
                //urlから日記の日付を取得(例:2021-04-1)
                $url = rtrim($url, '/');
                $date = substr($url, strrpos($url, '/') + 1);
                //日付($date)から年・月のみ取得(例:2021-04-1 -> 2021-04)
                return substr($date, 0, 7);
            }
        }
        return Carbon::now()->format('Y-m');
    }

    /**
     * getYM()の判定で使うルートを設定
     *
     * @return array
     */
    private static function set_routes()
    {
        return ['create', 'show', 'edit', 'partnerShow'];
    }

    /**
     * 2022-04-01 のような月初めの文字列を返却する
     *
     * @return string
     */
    private static function getYm_firstday()
    {
        return self::getYm() . '-01';
    }

    /**
     * 今日の日付と$set_dayの日数差を取得する
     *
     * @return array
     */
    public function diffDay()
    {
        $diff_days = [];
        $tagModel = new Tag();
        $tags =  $tagModel->where('user_id', \Auth::id())->get();

        if (isset($tags)) {
            foreach ($tags as $tag) {

                $set_day = new Carbon($tag['set_day']);
                $today = new Carbon(self::getNow());

                $diff_days[] = $set_day->diffInDays($today);
            }

            return $diff_days;
        }
    }

    /**
     *ログインユーザーの日記を入力済の日付を返す
     *
     * @return array
     */
    private static function getDiary()
    {
        $completed_diary = [];
        $diaryModel = new Diary();
        $diaries = $diaryModel->where('user_id', \Auth::id())->where('status', 1)->get(['diary_date']);

        foreach ($diaries as $diary) {
            $completed_diary[] = $diary['diary_date'];
        }
        return $completed_diary;
    }

    /**
     *ログインユーザーの日記を入力済の日付を返す
     *
     * @return array
     */
    private static function getPartnerDiary()
    {
        $completed_diary = [];
        $user = \Auth::user();
        if (isset($user['partner_id'])) {
            $diaryModel = new Diary();
            $diaries = $diaryModel->where('user_id', $user['partner_id'])->where('status', 1)->get(['diary_date']);

            foreach ($diaries as $diary) {
                $completed_diary[] = $diary['diary_date'];
            }
        }
        return $completed_diary;
    }
}
