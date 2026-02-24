<?php
namespace Pipe\String;

class Date
{
    static public $months = array(
        1	=> 'Январь',
        2	=> 'Февраль',
        3	=> 'Март',
        4	=> 'Апрель',
        5	=> 'Май',
        6	=> 'Июнь',
        7	=> 'Июль',
        8	=> 'Август',
        9	=> 'Сентябрь',
        10	=> 'Октябрь',
        11	=> 'Ноябрь',
        12	=> 'Декабрь'
    );

    static public $months2 = array(
        1	=> 'Января',
        2	=> 'Февраля',
        3	=> 'Марта',
        4	=> 'Апреля',
        5	=> 'Мая',
        6	=> 'Июня',
        7	=> 'Июля',
        8	=> 'Августа',
        9	=> 'Сентября',
        10	=> 'Октября',
        11	=> 'Ноября',
        12	=> 'Декабря'
    );

    static public $monthsShort = array(
        1	=> 'Янв',
        2	=> 'Фев',
        3	=> 'Мар',
        4	=> 'Апр',
        5	=> 'Май',
        6	=> 'Июн',
        7	=> 'Июл',
        8	=> 'Авг',
        9	=> 'Сен',
        10	=> 'Окт',
        11	=> 'Ноя',
        12	=> 'Дек'
    );

    static public $weekdays = [
        1   => 'Понедельник',
        2   => 'Вторник',
        3   => 'Среда',
        4   => 'Четверг',
        5   => 'Пятница',
        6   => 'Суббота',
        7   => 'Воскресенье',
    ];

    static public $weekdays2 = [
        1   => 'Понедельник',
        2   => 'Вторник',
        3   => 'Среду',
        4   => 'Четверг',
        5   => 'Пятницу',
        6   => 'Субботу',
        7   => 'Воскресенье',
    ];

    static public $weekdaysShort = [
        1   => 'Пн',
        2   => 'Вт',
        3   => 'Ср',
        4   => 'Чт',
        5   => 'Пт',
        6   => 'Сб',
        7   => 'Вс',
    ];

    /**
     * @param $str
     * @return \DateTime
     */
    static function timeToDt($str)
    {
        $time = \DateTime::createFromFormat('H:i:s', $str);
        if(!$time) {
            $time = \DateTime::createFromFormat('H:i', $str);
        }

        return $time;
    }

    static function toStr($date, $options = [], $pattern = 'Y-m-d H:i:s')
    {
        $options = [
            'day'    => true,
            'month'  => true,
            'year'   => true,
            'time'   => false,
        ] + $options;

        if(is_object($date)) {
            $dt = $date;
        } else {
            $dt = \DateTime::createFromFormat($pattern, $date);
        }

        $str = '';
        if($options['day']) {
            $str .= $dt->format('d');
        }

        if($options['month']) {
            $str .= ' ';

            if($options['month'] == 'short') {
                $str .= self::$monthsShort[$dt->format('n')];
            } elseif($options['day']) {
                $str .= self::$months2[$dt->format('n')];
            } else {
                $str .= self::$months[$dt->format('n')];
            }
        }

        if($options['year']) {
            $str .= ' ' . $dt->format('Y');
        }

        if($options['time']) {
            $str .= ' ' . $dt->format('H') . ':' . $dt->format('i');
        }

        return $str;
    }

    static function timeToStr($time) {
        $str = '';
        list($hours, $minutes) = explode(':', $time);

        if($hours && $hours != '00') {
            $str .= Numbers::declensionRu($hours, ['час', 'часа', 'часов']);
        }

        if($minutes && $minutes != '00') {
            $str .= ' ' . Numbers::declensionRu($minutes, ['минута', 'минуты', 'минут']);
        }

        if(!$str) {
            $str = '00:00';
        }

        return ltrim($str);
    }

    /**
     * @param $str
     * @return \DateTime
     */
    static public function parseDate($str)
    {
        if(!$str) {
            $str = '0000-00-00';
        }

        $time = \DateTime::createFromFormat('d.m.Y', $str);
        if(!$time) {
            $time = \DateTime::createFromFormat('Y-m-d', $str);
        }

        return $time;
    }

    /**
     * @param $str
     * @return \DateTime
     */
    static public function parseTime($str)
    {
        $str = str_replace('.', ':', $str);

        if(!$str) {
            $str = '00:00:00';
        }

        $time = \DateTime::createFromFormat('H:i:s', $str);
        if(!$time) {
            $time = \DateTime::createFromFormat('H:i', $str);
            if(!$time) {
                $time = \DateTime::createFromFormat('H', $str);
            }
        }

        return $time;
    }
}