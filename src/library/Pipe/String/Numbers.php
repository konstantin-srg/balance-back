<?php
namespace Pipe\String;

class Numbers
{
    static public function declension($number, $words, $lang = 'ru', $showNbr = true)
    {
        $str = ($showNbr ? $number . ' ' : '');

        switch ($lang) {
            case 'ru':
                $str .= self::declensionRu($number, $words[$lang], false);
                break;
            case 'en':
                $str .= self::declensionEn($number, $words[$lang]);
                break;
            case 'de':
                $str .= self::declensionDe($number, $words[$lang]);
                break;
        }

        return $str;
    }

    /**
     * example \Pipe\String\Numbers::declension($number, ['1 яблоко', '3 яблока', '5 яблок'])
     *
     * @param $number
     * @param $words
     * @param $showNbr
     * @return RandStr
     */
    static public function declensionRu($number, $words, $showNbr = true)
    {
        $str = ($showNbr ? $number . ' ' : '');

        $number = $number % 100;
        if ($number >= 11 && $number <= 19) {
            $str .= $words[2];
        } else {
            switch ($number % 10) {
                case (1):
                    $str .= $words[0];
                    break;
                case (2):
                case (3):
                case (4):
                    $str .= $words[1];
                    break;
                default:
                    $str .= $words[2];
            }
        }

        return $str;
    }

    static protected function declensionEn($number, $words)
    {
        if($number == 1) {
            return $words[0];
        } else {
            return $words[1];
        }
    }

    static protected function declensionDe($number, $words)
    {
        switch ($number % 10) {
            case (1):
                $str = $words[0];
                break;
            default:
                $str = $words[1];
        }

        return $str;
    }
}