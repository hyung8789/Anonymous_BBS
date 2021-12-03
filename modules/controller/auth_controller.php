<?php
/***
    https://www.geeksforgeeks.org/php-sessions/

    - 세션 : 서버 측에 정보를 저장
    - 쿠키 : 사용자 측에 정보를 저장
    ---
    서버와 연결이 해제되는 시점 (브라우저 종료) 혹은 php.ini의 세션 유지시간에 따라 세션 해제
 ***/

/**
 * 이메일 문자열에 대해 중복 이메일 존재 여부 판별
 * @param string $escapedEmail 검사를 수행할 특수 기호들에 대해 문자로 치환 된 이메일 문자열
 * @return bool 중복 이메일 존재 여부
 */
function isDuplicateEmailExists(string $escapedEmail)
{
    $dbConnection = getDBConnection();
    $queryResult = null; //쿼리 결과
    $retVal = false; //반환 값

    $sql = "SELECT * FROM 사용자 WHERE 이메일 = '{$escapedEmail}';";

    if ($queryResult = $dbConnection->query($sql)) {
        $retVal = ($queryResult->num_rows >= 1 ? true : false); //이미 존재 할 경우
        return $retVal;
    } else {
        throwException(new Exception("쿼리 오류"));
    }
}

/**
 * 로그인 여부 반환
 * @return bool 로그인 여부
 */
function isAlreadySignIn()
{
    if (session_status() !== PHP_SESSION_ACTIVE) { //세션이 활성화되지 않았을 경우
        session_start();
    }

    if (defined("ADMIN_MODE")){ //bypass auth
        return true;
    }

    if (isset($_SESSION[Key::EMAIL])) {
        if (
            strlen($_SESSION[Key::EMAIL]) <= getCharacterMaximumLengthFromSchema(Key::EMAIL) &&
            filter_var($_SESSION[Key::EMAIL], FILTER_VALIDATE_EMAIL) && isDuplicateEmailExists($_SESSION[Key::EMAIL])
        ) { //유효성 검사
            return true;
        }
    }

    if (isset($_COOKIE[Key::EMAIL])) {
        if (
            strlen($_COOKIE[Key::EMAIL]) <= getCharacterMaximumLengthFromSchema(Key::EMAIL) &&
            filter_var($_COOKIE[Key::EMAIL], FILTER_VALIDATE_EMAIL) && isDuplicateEmailExists($_COOKIE[Key::EMAIL])
        ) { //유효성 검사
            return true;
        }
    }

    return false;
}

/**
 * 인증 타입에 따라 사용자 인증 처리
 * @param int $authType 인증 타입 
 * @return int 작업 상태 (SUCCESS : 인증 성공, FAIL : 인증 실패)
 */
function authProc(int $authType)
{
    $dbConnection = getDBConnection();
    $queryResult = null; //쿼리 결과
    $retVal = FAIL; //작업 상태

    if (session_status() !== PHP_SESSION_ACTIVE) { //세션이 활성화되지 않았을 경우
        session_start();
    }

    switch ($authType) {
        case AuthType::SIGN_IN:
            $isValidExecCond = isset($_POST[Key::EMAIL]) && isset($_POST[Key::PASSWORD]) &&
                strlen($_POST[Key::EMAIL]) <= getCharacterMaximumLengthFromSchema(Key::EMAIL) &&
                strlen($_POST[Key::PASSWORD]) <= getCharacterMaximumLengthFromSchema(Key::PASSWORD) &&
                filter_var($_POST[Key::EMAIL], FILTER_VALIDATE_EMAIL);

            if (!$isValidExecCond) {
                throwException(new Exception("잘못 된 실행 조건 : 로그인에 대한 예외 발생"), false);
                goto END_PROC;
            }

            $escapedEmail = htmlentities(mysqli_real_escape_string($dbConnection, $_POST[Key::EMAIL])); //이메일
            $sql = "SELECT 비밀번호 FROM 사용자 WHERE 이메일 = '{$escapedEmail}';";

            if ($queryResult = $dbConnection->query($sql)) {
                $row = $queryResult->fetch_assoc(); //결과 행

                if (password_verify($_POST[Key::PASSWORD], $row["비밀번호"])) { //비밀번호가 일치 할 경우
                    $_SESSION[Key::EMAIL] = $escapedEmail; //세션 등록 (로그인 성공)

                    if (isset($_POST[Key::REMEMBER_ME]) && $_POST[Key::REMEMBER_ME]) { //Remember me
                        $retVal = (setcookie(Key::EMAIL, $escapedEmail, time() + 60 * 60 * 24 * 30, "/") ? SUCCESS : FAIL); //30일 저장
                    } else {
                        $retVal = SUCCESS;
                    }
                }
            }

            break;

        case AuthType::SIGN_UP:
            $isValidExecCond = isset($_POST[Key::EMAIL]) && isset($_POST[Key::PASSWORD]) && isset($_POST[Key::CONFIRM_PASSWORD]) &&
                $_POST[Key::PASSWORD] == $_POST[Key::CONFIRM_PASSWORD] &&
                strlen($_POST[Key::EMAIL]) <= getCharacterMaximumLengthFromSchema(Key::EMAIL) &&
                strlen($_POST[Key::PASSWORD]) <= getCharacterMaximumLengthFromSchema(Key::PASSWORD) &&
                filter_var($_POST[Key::EMAIL], FILTER_VALIDATE_EMAIL) && !isDuplicateEmailExists($_POST[Key::EMAIL]);

            if (!$isValidExecCond) {
                throwException(new Exception("잘못 된 실행 조건 : 회원가입에 대한 예외 발생"), false);
                goto END_PROC;
            }

            $escapedEmail = htmlentities(mysqli_real_escape_string($dbConnection, $_POST[Key::EMAIL])); //이메일
            $encPassword = password_hash($_POST[Key::PASSWORD], PASSWORD_BCRYPT); //사용자 입력 비밀번호에 대한 암호화된 문자열
            $sql = "INSERT INTO 사용자 VALUES('{$escapedEmail}', '{$encPassword}');";

            if ($dbConnection->query($sql)) {
                $retVal = SUCCESS;
            }

            break;

        case AuthType::SIGN_OUT:
            $retVal = ((setcookie(Key::EMAIL, "") ? SUCCESS : FAIL) && (session_destroy() ? SUCCESS : FAIL));
            break;

        default:
            throwException(new Exception("잘못 된 인증 타입"));
    }

 END_PROC:
    return $retVal;
}
