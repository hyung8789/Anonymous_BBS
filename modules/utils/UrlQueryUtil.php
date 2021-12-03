<?php
class UrlQueryUtil //URL 쿼리 유틸리티
{
    /**
     * 호출자의 현재 파일 이름 반환
     * @return string 호출자의 현재 파일 이름
     */
    public static function getCallerFileName()
    {
        // https://www.php.net/manual/en/function.debug-backtrace.php
        // https://www.php.net/manual/en/function.basename

        static $callerFileName = null; //호출자의 현재 파일 이름

        if (!isset($callerFileName)) { //호출자의 현재 파일 이름이 설정되지 않았으면
            $backTraceArray = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS); //호출 위치 역추적 배열 (추가 인자 무시)
            $callerFileName = basename($backTraceArray[0]["file"]);
        }

        return $callerFileName;
    }

    /**
     * 호출자의 URL에 대한 GET 쿼리 배열 반환
     * @return array 호출자의 URL에 대한 GET 쿼리 배열 (Key : Key.php에 따름)
     */
    public static function getCallerUrlQueryArray()
    {
        $retVal = array(); //URL에 대한 GET 쿼리 배열 (Key : Key.php에 따름)

        //echo var_dump($_GET);
        foreach ($_GET as $key => $val) { //HTTP GET의 각 요청에 대해
            $retVal = array_merge($retVal, [$key => $val]);
        }

        return $retVal;
    }

    /**
     * 대상 URL의 GET 쿼리 배열 반환
     * @param string $targetUrl 대상 URL 문자열
     * @return array 대상 URL의 GET 쿼리 배열 (Key : Key.php에 따름)
     */
    public static function getUrlQueryArray(string $targetUrl)
    {
        // https://www.php.net/manual/en/function.parse-url.php
        // https://www.php.net/manual/en/function.parse-str.php

        $parsedUrlQueryString = parse_url($targetUrl, PHP_URL_QUERY);
        $retVal = null;
        parse_str($parsedUrlQueryString, $retVal);
        return $retVal;
    }

    /**
     * URL에 대한 GET 쿼리 배열 생성
     * @param array $queryArrays 가변 GET 쿼리 배열
     * @return array 생성 된 URL에 대한 GET 쿼리 배열 (Key : Key.php에 따름)
     */
    public static function genUrlQueryArray(array ...$queryArrays)
    {
        $retVal = array(); //URL에 대한 GET 쿼리 배열 (Key : Key.php에 따름)

        foreach ($queryArrays as $queryArray) { //각 쿼리에 대해
            $retVal = array_merge($retVal, $queryArray);
        }

        return $retVal;
    }

    /**
     * 대상 GET 쿼리 배열과 제외 할 GET 쿼리 배열의 차이를 제외 한 URL에 대한 GET 쿼리 배열 생성
     * @param array $targetQueryArray 대상 GET 쿼리 배열
     * @param array $diffQueryArray 제외 할 GET 쿼리 배열
     * @return array 대상 GET 쿼리 배열로부터 제외 할 GET 쿼리 배열이 제외 된 GET 쿼리 배열
     */
    public static function genUrlQueryArrayExcludeDiff(array $targetQueryArray, array $diffQueryArray)
    {
        return array_diff($targetQueryArray, $diffQueryArray);
    }

    /**
     * 대상 GET 쿼리 배열과 제외 할 GET 쿼리 배열의 키를 비교하여 차이를 제외 한 URL에 대한 GET 쿼리 배열 생성
     * @param array $targetQueryArray 대상 GET 쿼리 배열
     * @param array $diffQueryKeyArray 제외 할 GET 쿼리 키 배열 (Key : Key.php에 따름)
     * @return array 대상 GET 쿼리 배열로부터 제외 할 GET 쿼리 키들이 제외 된 GET 쿼리 배열
     */
    public static function genUrlQueryArrayExcludeDiffFromKey(array $targetQueryArray, array $diffQueryKeyArray)
    {
        return array_diff_key($targetQueryArray, $diffQueryKeyArray);
    }

    /**
     * 대상 URL에 대해 GET 쿼리를 포함한 URL 문자열 반환
     * @param string $targetUrl 대상 URL 문자열
     * @param array $queryArray GET 쿼리 배열 (Key : Key.php에 따름)
     * @return string 쿼리를 포함한 URL 문자열
     */
    public static function genUrlIncQuery(string $targetUrl, array $queryArray)
    {
        return $targetUrl . "?" . http_build_query($queryArray);
    }

    /**
     * 대상 URL에 대해 병합 된 GET 쿼리를 포함한 URL 문자열 반환
     * @param string $targetUrl 대상 URL 문자열
     * @param array $oldQueryArray 병합을 수행 할 이전 GET 쿼리 배열 (Key : Key.php에 따름)
     * @param array $newQueryArray 병합을 수행 할 새 GET 쿼리 배열 (Key : Key.php에 따름)
     * @return string 쿼리를 포함한 URL 문자열
     */
    public static function genUrlIncMergeQuery(string $targetUrl, array $oldQueryArray, array $newQueryArray)
    {
        // https://www.php.net/manual/en/reserved.variables.server.php
        // https://www.php.net/manual/en/function.http-build-query.php

        $mergeQuery = array_merge($oldQueryArray, $newQueryArray); //병합 된 GET 쿼리
        return $targetUrl . "?" . http_build_query($mergeQuery);
    }

    private function __construct()
    {
    }
}
