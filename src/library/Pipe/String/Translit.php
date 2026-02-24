<?php
namespace Pipe\String;

class Translit
{
  /**
   * @param $str
   * @return string
   */
    static function ruToEn($str)
    {
        $tr = array(
            "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
            "Д"=>"D","Е"=>"E","Ё"=>"E","Ж"=>"J","З"=>"Z","И"=>"I",
            "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
            "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
            "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
            "Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"YI","Ь"=>"",
            "Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
            "в"=>"v","г"=>"g","д"=>"d","е"=>"e", "ё" => "e", "ж"=>"j",
            "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
            "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
            "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
            "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
            "ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya"
        );

        $trStr = strtr($str,$tr);

        return $trStr;
    }

    /**
     * @param $str
     * @return string
     */
    static function enToRu($str)
    {
        $tr = array(
            "A" => "А", "B" => "Б", "V" => "В", "G" => "Г", "D" => "Д",
            "E" => "Е", "J" => "Ж", "Z" => "З", "I" => "И", "Y" => "Й",
            "K" => "К", "L" => "Л", "M" => "М", "N" => "Н", "O" => "О",
            "P" => "П", "R" => "Р", "S" => "С", "T" => "Т", "U" => "У",
            "F" => "Ф", "H" => "Х", "TS" => "Ц", "CH" => "Ч", "SH" => "Ш",
            "SCH" => "Щ", "YI" => "Ы", "YU" => "Ю", "YA" => "Я", "a" => "а",
            "b" => "б", "v" => "в", "g" => "г", "d" => "д", "e" => "е",
            "j" => "ж", "z" => "з", "i" => "и", "y" => "ъ", "k" => "к",
            "l" => "л", "m" => "м", "n" => "н", "o" => "о", "p" => "п",
            "r" => "р", "s" => "с", "t" => "т", "u" => "у", "f" => "ф",
            "h" => "х", "ts" => "ц", "ch" => "ч", "sh" => "ш", "sch" => "щ",
            "yi" => "ы", "yu" => "ю", "ya" => "я"
        );

        $trStr = strtr($str,$tr);

        return $trStr;
    }

    /**
     * @param $str
     * @return string
     */
    static function url($str) {
        $trStr = self::ruToEn($str);

        $trStr = str_replace('-', ' ', $trStr);
        $trStr = str_replace('/\s+/', ' ', $trStr);
        $trStr = str_replace(' ', '-', $trStr);
        $trStr = preg_replace('/[^A-Za-z0-9_\-]/', '', $trStr);

        return strtolower($trStr);
    }

    static public function searchVariants($query) {
        $query = mb_strtolower($query);

        $result = [$query];

        $replace = [
            'q' => 'й', 'w' => 'ц', 'e' => 'у', 'r' => 'к', 't' => 'е', 'y' => 'н', 'u' => 'г', 'i' => 'ш', 'o' => 'щ', 'p' => 'з',
            '[' => 'х', ']' => 'ъ', 'a' => 'ф', 's' => 'ы', 'd' => 'в', 'f' => 'а', 'g' => 'п', 'h' => 'р', 'j' => 'о', 'k' => 'л',
            'l' => 'д', ';' => 'ж', '\'' => 'э', 'z' => 'я', 'x' => 'ч', 'c' => 'с', 'v' => 'м', 'b' => 'и', 'n' => 'т', 'm' => 'ь',
            ',' => 'б', '.' => 'ю'];

        $result[] = str_replace(array_keys($replace), $replace, $query);
        $result[] = str_replace($replace, array_keys($replace), $query);
        /*$result[] = self::ruToEn($query);
        $result[] = self::enToRu($query);

        $tmp = self::ruToEn($result[1]);
        if($tmp) {
            $result[] = $tmp;
        }*/

        $result = array_unique($result);

        return $result;
    }
}