<?php
namespace Pipe\String;

class RandStr
{
  /**
   * @param int $lenght
   * @return false|string
   */
    static public function randomString($lenght = 15)
    {
        return substr(md5(rand()), 0, $lenght);
    }
}