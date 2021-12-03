<?php
/***
    < SQL Injection, XSS 방어 >
    https://www.php.net/manual/en/security.database.sql-injection.php
    https://www.php.net/manual/en/mysqli.real-escape-string.php
    https://www.tutorialspoint.com/security_testing/testing_cross_site_scripting.htm
    https://owasp.org/www-project-web-security-testing-guide/latest/4-Web_Application_Security_Testing/07-Input_Validation_Testing/01-Testing_for_Reflected_Cross_Site_Scripting
    https://cheatsheetseries.owasp.org/cheatsheets/XSS_Filter_Evasion_Cheat_Sheet.html
    ---
    1) 입력 문자열에 대해 특수 기호들을 문자로 치환
    2) 모든 입력값에 대해 강제 캐스팅

    < 비밀번호에 대한 보안 처리 >
    https://www.php.net/manual/en/function.password-hash.php
    https://www.php.net/manual/en/function.password-verify.php
    https://en.wikipedia.org/wiki/Bcrypt
    ---
    동일한 비밀번호에 대해 솔트를 이용해 다른 암호화 문자열을 생성하는
    CRYPT_BLOWFISH를 사용하여 60글자의 고정 길이 암호화 문자열 (해시 문자열)로 변환

    < 기타 입력 값에 대한 보안 처리 >
    클라이언트에서 오는 모든 입력 값을 이용해 실제 서버에서 수행 되기 전 입력 값에 대해 검증 (수정, 삭제 시 권한 확인)
 ***/

 define("HOST", "localhost");
 define("USERNAME", "root");
 define("PASSWORD", "1234");
 define("DATABASE", "bbsdb");
 define("PORT", 3306);

/**
 * DB 연결 반환
 * @return mysqli DB 연결
 */
function getDBConnection()
{
    static $dbConnection = null;

    if (!isset($dbConnection)) { //DB 연결이 설정되지 않았으면
        $dbConnection = new mysqli(HOST, USERNAME, PASSWORD, DATABASE, PORT); //DB 연결
        $dbConnection->set_charset("utf-8");
    }

    return $dbConnection;
}

/**
 * 키 상수 (Key.php)에 따른 DB 스키마 상의 바이트 단위 최대 길이 정수 반환
 * @param string $key 키 상수 (Key.php)
 * @return int DB 스키마 상의 바이트 단위 최대 길이 정수
 */
function getCharacterMaximumLengthFromSchema(string $key)
{
    /***
        K) 대상 키 기존 할당 여부 (T : 할당, F : 미 할당)
        S) 파라미터 키와 대상 키 동일 여부 (단, 대상 키는 기존 할당 된 상태)
        B) DB 스키마 상의 바이트 단위 최대 길이 정수 할당 여부 (T : 할당, F : 미 할당)
        D) 이에 따른 수행 작업

        K | S | B | D
        T   T   T   기존 DB 스키마 상의 바이트 단위 최대 길이 정수 반환
        T   T   F   오류 : 이전에 DB 스키마 상의 바이트 단위 최대 길이 정수가 할당되었어야 함
        T   F   -   대상 키 할당, DB 스키마 상의 바이트 단위 최대 길이 정수 할당 및 반환
        F   -   -   대상 키 할당, DB 스키마 상의 바이트 단위 최대 길이 정수 할당 및 반환
     ***/

    static $targetKey = null; //대상 키
    static $characterMaximumLength = null; //DB 스키마 상의 바이트 단위 최대 길이 정수

    $sql = null;

    if (!isset($key)) {
        throwException(new Exception("할당되지 않은 키 상수"));
    }

    if (isset($targetKey) && ($targetKey == $key) && isset($characterMaximumLength)) { //T T T
        goto END_PROC;
    } else if (isset($targetKey) && ($targetKey == $key) && !isset($characterMaximumLength)) { //T T F
        throwException(new Exception("이전에 DB 스키마 상의 바이트 단위 최대 길이 정수가 할당되었어야 함"));
    } else { // T F - or F - -
        goto ALLOCATE_PROC;
    }

ALLOCATE_PROC:
    $database = constant("DATABASE");
    $targetKey = $key;
    $dbConnection = getDBConnection();

    switch ($targetKey) {
        case Key::EMAIL:
            $sql = "SELECT CHARACTER_MAXIMUM_LENGTH 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='사용자' AND COLUMN_NAME='이메일';";
            break;

        case Key::PASSWORD:
            $sql = "SELECT CHARACTER_MAXIMUM_LENGTH 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA='{$database}' AND TABLE_NAME='사용자' AND COLUMN_NAME='비밀번호';";
            break;

        case Key::POST_PASSWORD:
            $sql = "SELECT CHARACTER_MAXIMUM_LENGTH 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA='{$database}' AND TABLE_NAME='게시글' AND COLUMN_NAME='글_비밀번호';";
            break;

        case Key::REPLY_PASSWORD:
            $sql = "SELECT CHARACTER_MAXIMUM_LENGTH 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA='{$database}' AND TABLE_NAME='댓글' AND COLUMN_NAME='댓글_비밀번호';";
            break;

        case Key::POST_TITLE:
            $sql = "SELECT CHARACTER_MAXIMUM_LENGTH 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA='{$database}' AND TABLE_NAME='게시글' AND COLUMN_NAME='글_제목';";
            break;

        case Key::POST_ATTACHMENT_ORIGINAL_FILE_NAME:
            $sql = "SELECT CHARACTER_MAXIMUM_LENGTH 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA='{$database}' AND TABLE_NAME='게시글' AND COLUMN_NAME='첨부파일_원본이름';";
            break;

        default:
            throwException(new Exception("잘못 된 키 상수"));
    }

    if ($queryResult = $dbConnection->query($sql)) {
        $row = $queryResult->fetch_row(); //결과 행
        $characterMaximumLength = (int)$row[0];
        unset($queryResult);
    } else {
        throwException(new Exception("쿼리 오류"));
    }

    goto END_PROC;

END_PROC:
    return $characterMaximumLength;
}
