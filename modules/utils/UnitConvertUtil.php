<?php
class UnitConvertUtil //단위 변환기
{
    // https://www.php.net/manual/en/faq.using.php#faq.using.shorthandbytes

    /**
     * 바이트 단위 정수에 대해 약식 표기 단위 문자열로 변환
     * @param int $targetBytes 바이트 단위 대상 정수
     * @param string $toShorthandNotation 대소문자를 구분하지 않는 변환 할 약식 표기 단위 (K (for Kilobytes), M (for Megabytes), G (for Gigabytes))
     * @param int $precision 반올림할 선택적 소수 자릿수 (0 : 반올림하지 않음, 양수 : 소수점 이후 유효 숫자, 음수 : 소수점 전의 유효 숫자)
     * @return string 변환 된 약식 표기 단위 문자열
     */
    public static function bytesToShorthandNotation(int $targetBytes, string $toShorthandNotation, int $precision = 0)
    {
        $retVal = $targetBytes;
        $to = strtolower($toShorthandNotation); //변환 할 약식 표기 단위

        if (!in_array($to, array("g", "m", "k"))) { //변환 할 약식 표기 단위 범위 내에 해당되지 않을 경우
            throwException(new Exception("잘못 된 변환 할 약식 표기 단위"));
        }

        switch ($to) {
            case 'g': //G (for Gigabytes)
                $retVal /= 1024;

            case 'm': //M (for Megabytes)
                $retVal /= 1024;

            case 'k': //K (for Kilobytes)
                $retVal /= 1024;
        }

        // https://www.php.net/manual/en/function.round
        $retVal = ($precision == 0 ? $retVal : round($retVal, $precision));
        return (string)($retVal . strtoupper($to));
    }

    /**
     * 약식 표기 단위 문자열에 대해 바이트 단위 정수로 변환
     * @param string $targetShorthandNotation 약식 표기 단위 대상 문자열
     * @return int 변환 된 바이트 단위 정수
     */
    public static function shorthandNotationToBytes(string $targetShorthandNotation)
    {
        $retVal = trim($targetShorthandNotation);
        $suffix = strtolower($retVal[strlen($retVal) - 1]); //접미사
        $retVal = intval($retVal); //정수로 변환

        switch ($suffix) {
            case 'g': //G (for Gigabytes)
                $retVal *= 1024;

            case 'm': //M (for Megabytes)
                $retVal *= 1024;

            case 'k': //K (for Kilobytes)
                $retVal *= 1024;
        }

        return $retVal;
    }

    private function __construct()
    {
    }
}
