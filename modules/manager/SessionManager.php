<?php

class SessionManager //세션 관리자
{    
    /**
     * 세션 데이터 할당 여부 반환
     * @param string $key 문자열 키
     * @return bool 세션 데이터 할당 여부
     */
    public static function isSessionDataAllocated(string $key)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) { //세션이 활성화되지 않았을 경우
            session_start();
        }

        if (isset($_SESSION[$key])) { //이미 해당 키를 사용하고 있으면
            return true;
        }

        return false;
    }

    /**
     * 세션 데이터 할당
     * @param string $key 문자열 키
     * @param mixed $data 저장 할 데이터
     * @param bool $isImmutable 불변성 여부 (true : 해당 문자열 키에 대해 재 할당 불가, false : 해당 문자열 키에 대해 재 할당 가능)
     * @return void 
     */
    public static function setSessionData(string $key, $data, bool $isImmutable = false)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) { //세션이 활성화되지 않았을 경우
            session_start();
        }

        if (self::isSessionDataAllocated($key)) { //이미 해당 키를 사용하고 있으면
            if ((bool)$_SESSION[$key]["isImmutable"]) { //불변 속성을 가지고 있으면
                throwException(new Exception("불변성 키에 대한 Overwrite 시도 : $key"), false);
                return;
            }
        }

        $_SESSION[$key] = array("data" => $data, "isImmutable" => $isImmutable);
    }

    /**
     * 세션 데이터 반환
     * @param string $key 문자열 키
     * @return mixed 저장 된 데이터
     */
    public static function getSessionData(string $key)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) { //세션이 활성화되지 않았을 경우
            session_start();
        }

        if (!self::isSessionDataAllocated($key)) {
            throwException(new Exception("할당되지 않은 데이터에 대한 접근"), false);
            return null;
        } else {
            return $_SESSION[$key]["data"];
        }
    }

    /**
     * 세션 데이터 반환 및 파괴
     * @param string $key 문자열 키
     * @return mixed 저장 된 데이터
     */
    public static function getSessionDataAndDestroy(string $key)
    {
        $retVal = null;

        if (session_status() !== PHP_SESSION_ACTIVE) { //세션이 활성화되지 않았을 경우
            session_start();
        }

        $retVal = self::getSessionData($key);
        self::destroySessionData($key);
        return $retVal;
    }

    /**
     * 세션 데이터 파괴
     * @param string $key 문자열 키
     * @return void 
     */
    public static function destroySessionData(string $key)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) { //세션이 활성화되지 않았을 경우
            session_start();
        }

        if(self::isSessionDataAllocated($key)){
            unset($_SESSION[$key]);
        }
    }
}