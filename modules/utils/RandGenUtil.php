<?php

class RandGenUtil //난수 생성기
{
    /**
     * 생성 할 문자열 길이에 따라 임의의 문자열 생성
     * @param int $length 생성 할 문자열 길이 
     * @return string 생성 된 임의의 문자열
     */
    public static function genRandString($length = 256)
    {
        $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
        $charactersLength = count($characters) - 1;
        $retVal = "";

        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $charactersLength); //메르센 트위스터에 의해 생성 된 임의의 숫자
            $retVal .= $characters[$rand];
        }

        return $retVal;
    }

    private function __construct()
    {
    }
}
