<?php

/**
 * 특정 게시글 비밀번호 일치 여부 판별
 * @param int $postNum 특정 게시글 번호
 * @param string $password 일치 여부를 판별 할 사용자로부터 입력받은 게시글 비밀번호
 * @return bool 일치 여부
 */
function isPostPasswordMatch(int $postNum = -1, string $password = "")
{
    if (defined("ADMIN_MODE")){ //bypass auth
        return true;
    }

    $dbConnection = getDBConnection();
    $isValidExecCond = $postNum > 0 && $postNum <= PHP_INT_MAX && $password != "" &&
        isAlreadySignIn();

    if (!$isValidExecCond) {
        throwException(new Exception("잘못 된 실행 조건"));
    }

    $sql = "SELECT 글_비밀번호
            FROM 게시글
            WHERE 글_번호 = '{$postNum}';";

    if ($queryResult = $dbConnection->query($sql)) {
        $row = $queryResult->fetch_assoc(); //결과 행

        if (password_verify($password, $row["글_비밀번호"])) { //비밀번호가 일치 할 경우
            return true;
        }
    } else {
        throwException(new Exception("쿼리 오류 : $sql"));
    }

    return false;
}

/**
 * 특정 댓글 비밀번호 일치 여부 판별
 * @param int $postNum 특정 댓글 번호
 * @param string $password 일치 여부를 판별 할 사용자로부터 입력받은 댓글 비밀번호
 * @return bool 일치 여부
 */
function isReplyPasswordMatch($replyNum = -1, string $password = "")
{
    if (defined("ADMIN_MODE")){ //bypass auth
        return true;
    }
    
    $dbConnection = getDBConnection();
    $isValidExecCond = $replyNum > 0 && $replyNum <= PHP_INT_MAX && $password != "" &&
        isAlreadySignIn();

    if (!$isValidExecCond) {
        throwException(new Exception("잘못 된 실행 조건"));
    }

    $sql = "SELECT 댓글_비밀번호
            FROM 댓글
            WHERE 댓글_번호 = '{$replyNum}';";

    if ($queryResult = $dbConnection->query($sql)) {
        $row = $queryResult->fetch_assoc(); //결과 행

        if (password_verify($password, $row["댓글_비밀번호"])) { //비밀번호가 일치 할 경우
            return true;
        }
    } else {
        throwException(new Exception("쿼리 오류 : $sql"));
    }

    return false;
}

/**
 * 고유 사용자에 대한 특정 게시글 조회수 증가
 * @param int $postNum 특정 게시글 번호
 * @return int 작업 상태
 */
function increasePostViewCount(int $postNum = -1)
{
    $dbConnection = getDBConnection();
    $retVal = FAIL;
    $isValidExecCond = $postNum > 0 && $postNum <= PHP_INT_MAX &&
        isAlreadySignIn();

    if (!$isValidExecCond) {
        throwException(new Exception("잘못 된 실행 조건"));
    }

    $postNumArray = array(); //게시글 번호 배열
    $isAlreadyRead = false; //이미 읽은 게시글 여부

    if (isset($_COOKIE[Key::POST_NUM])) { //쿠키에 기존에 읽은 게시글 번호가 존재하면
        $postNumArray = json_decode($_COOKIE[Key::POST_NUM], true); //게시글 번호에 대한 연관 배열로 변환
        //echo var_dump($postNumArray);

        // https://www.php.net/manual/en/language.operators.comparison.php
        $isAlreadyRead = (array_search($postNum, $postNumArray) !== false ? true : false); //이미 읽은 게시글 판별
    }

    if (!$isAlreadyRead) { //아직 읽지 않은 게시글일 경우
        array_push($postNumArray, $postNum); //append

        $sql = "CALL 게시글_조회수_증가({$postNum})";
        if (!($dbConnection->query($sql))) {
            throwException(new Exception("쿼리 오류 : $sql"));
        }
    }

    $retVal = (setcookie(Key::POST_NUM, json_encode($postNumArray), time() + 60 * 60 * 24, "/") ? SUCCESS : FAIL); //읽은 게시글 번호에 대해 하루 저장
    unset($postNumArray);

    return $retVal;
}
/**
 * 전체 게시글에 대해 전체 페이지 수 반환
 * @param string $searchKeyword 검색어
 * @return int 전체 페이지 수 (최소 1)
 */
function getTotalPostPageCount(string $searchKeyword = "")
{
    $dbConnection = getDBConnection();
    $sql = null;

    if ($searchKeyword != "") { //검색어가 존재 시
        $escapedSearchKeyword = htmlentities(mysqli_real_escape_string($dbConnection, $searchKeyword)); //검색어
        $sql = "SELECT COUNT(*)
                FROM 게시글
                WHERE 게시글.글_제목 LIKE '{%$escapedSearchKeyword%}' OR 
                게시글.글_내용 LIKE '{%$escapedSearchKeyword%}';";
    } else { //검색어가 존재하지 않을 시
        $sql = "SELECT COUNT(*) 
                FROM 게시글;";
    }

    if ($queryResult = $dbConnection->query($sql)) {
        $row = $queryResult->fetch_row(); //결과 행
        $totalCount = $row[0]; //전체 게시글 수
        return (ceil($totalCount / PaginationOption::PAGE_PER_POST) == 0) ? 1 : ceil($totalCount / PaginationOption::PAGE_PER_POST); //전체 게시글 수 / 페이지 당 게시글 수 (최소 1페이지)

    } else {
        throwException(new Exception("쿼리 오류 : $sql"));
    }
}

/**
 * 특정 게시글에 종속 된 댓글에 대해 전체 페이지 수 반환
 * @param int $postNum 특정 게시글의 번호
 * @return int 전체 페이지 수 (최소 1)
 */
function getTotalReplyPageCount(int $postNum = -1)
{
    $dbConnection = getDBConnection();
    $isValidExecCond = $postNum > 0 && $postNum <= PHP_INT_MAX &&
        isAlreadySignIn();

    if (!$isValidExecCond) {
        throwException(new Exception("잘못 된 실행 조건"));
    }

    $sql = "SELECT COUNT(*) 
            FROM 댓글 
            INNER JOIN 게시글 ON 게시글.글_번호 = 댓글.글_번호
            WHERE 게시글.글_번호={$postNum};";

    if ($queryResult = $dbConnection->query($sql)) {
        $row = $queryResult->fetch_row(); //결과 행
        $totalCount = $row[0]; //특정 게시글에 대한 전체 댓글 수
        return (ceil($totalCount / PaginationOption::PAGE_PER_REPLY) == 0) ? 1 : ceil($totalCount / PaginationOption::PAGE_PER_REPLY); //특정 게시글에 대한 전체 댓글 수 / 페이지 당 댓글 수 (최소 1페이지)

    } else {
        throwException(new Exception("쿼리 오류 : $sql"));
    }
}

/**
 * 게시글 목록 가져오기
 * @param int $postPageNum 게시글 페이지 번호
 * @param string $searchKeyword 검색어
 * @return array 게시글에 대한 다중 키를 가진 배열 (Key : Key.php에 따름)
 */
function getPostList(int $postPageNum = 1, string $searchKeyword = "")
{
    $dbConnection = getDBConnection();
    $isValidExecCond = $postPageNum > 0 && $postPageNum <= PHP_INT_MAX &&
        isAlreadySignIn();

    if (!$isValidExecCond) {
        throwException(new Exception("잘못 된 실행 조건"));
    }

    $retVal = array(); //게시글에 대한 다중 키를 가진 배열 (Key : Key.php에 따름)
    $startOffset = ($postPageNum - 1) * PaginationOption::PAGE_PER_POST; //페이지 단위 출력을 위한 시작 오프셋
    $sql = null;

    if ($searchKeyword != "") { //검색어가 존재 시
        $escapedSearchKeyword = htmlentities(mysqli_real_escape_string($dbConnection, $searchKeyword)); //검색어
        $sql = "SELECT 게시글.글_번호, 게시글.글_제목, 게시글.작성일, 게시글.조회수, 
                IF(게시글.첨부파일_저장이름 IS NOT null, TRUE, FALSE) as 첨부파일_존재여부, 
                COUNT(댓글.댓글_번호) as 댓글_수
                FROM 게시글
                LEFT OUTER JOIN 댓글 
                ON 게시글.글_번호 = 댓글.글_번호
                WHERE 게시글.글_제목 LIKE '%{$escapedSearchKeyword}%' OR 게시글.글_내용 LIKE '%{$escapedSearchKeyword}%'
                GROUP BY 게시글.글_번호
                ORDER BY 작성일 DESC, 글_번호 DESC
                LIMIT {$startOffset}, " . PaginationOption::PAGE_PER_POST . ";"; //시작 오프셋부터 페이지 당 게시글 수만큼 가져옴

    } else { //검색어가 존재하지 않을 시
        $sql = "SELECT 게시글.글_번호, 게시글.글_제목, 게시글.작성일, 게시글.조회수, 
                IF(게시글.첨부파일_저장이름 IS NOT null, TRUE, FALSE) as 첨부파일_존재여부, 
                COUNT(댓글.댓글_번호) as 댓글_수
                FROM 게시글
                LEFT OUTER JOIN 댓글 
                ON 게시글.글_번호 = 댓글.글_번호
                GROUP BY 게시글.글_번호
                ORDER BY 작성일 DESC, 글_번호 DESC
                LIMIT {$startOffset}, " . PaginationOption::PAGE_PER_POST . ";"; //시작 오프셋부터 페이지 당 게시글 수만큼 가져옴
    }

    if ($queryResult = $dbConnection->query($sql)) {
        while ($row = $queryResult->fetch_array()) { //결과 행들에 대해
            array_push($retVal, $row); //append
        }
    } else {
        throwException(new Exception("쿼리 오류 : $sql"));
    }

    return $retVal;
}

/**
 * 특정 게시글 가져오기
 * @param int $postNum 특정 게시글 번호
 * @return array|null 게시글에 대한 다중 키를 가진 배열 (Key : Key.php에 따름) 혹은 존재하지 않을 시 null 반환
 */
function getSpecificPost(int $postNum = -1)
{
    $dbConnection = getDBConnection();
    $isValidExecCond = $postNum > 0 && $postNum <= PHP_INT_MAX &&
        isAlreadySignIn();

    if (!$isValidExecCond) {
        throwException(new Exception("잘못 된 실행 조건"));
    }

    $retVal = null; //반환 값
    $sql = "SELECT 게시글.글_번호, 게시글.글_제목, 게시글.글_내용, 게시글.작성일, 게시글.조회수, 
            게시글.첨부파일_원본이름,
            게시글.첨부파일_저장이름,
            COUNT(댓글.댓글_번호) as 댓글_수
            FROM 게시글
            LEFT OUTER JOIN 댓글
            ON 게시글.글_번호 = 댓글.글_번호
            WHERE 게시글.글_번호 = {$postNum}";

    if ($queryResult = $dbConnection->query($sql)) {
        $row = $queryResult->fetch_array(); //결과 행에 대해

        if ($row["글_번호"] != null) { //게시글이 존재 시
            $retVal = array();
            array_push($retVal, $row); //append
        }
    } else {
        throwException(new Exception("쿼리 오류 : $sql"));
    }

    return $retVal;
}

/**
 * 특정 게시글에 종속 된 첨부파일에 대해 서버에 저장 된 파일 이름 가져오기
 * @param int $postNum 특정 게시글 번호
 * @return string 특정 게시글에 종속 된 첨부파일에 대해 서버에 저장 된 파일 이름
 */
function getSpecificPostUploadedFileName(int $postNum = -1)
{
    $dbConnection = getDBConnection();
    $isValidExecCond = $postNum > 0 && $postNum <= PHP_INT_MAX &&
        isAlreadySignIn();

    if (!$isValidExecCond) {
        throwException(new Exception("잘못 된 실행 조건"));
    }

    $sql = "SELECT 게시글.첨부파일_저장이름
            FROM 게시글
            WHERE 게시글.글_번호 = {$postNum}";

    if ($queryResult = $dbConnection->query($sql)) {
        $row = $queryResult->fetch_row(); //결과 행
        return $row[0]; //특정 게시글에 종속 된 첨부파일에 대해 서버에 저장 된 파일 이름

    } else {
        throwException(new Exception("쿼리 오류 : $sql"));
    }
}

/**
 * 게시글 생성
 * @return int 작업 상태
 */
function createPost()
{
    $dbConnection = getDBConnection();
    $isValidExecCond = isset($_POST[Key::POST_TITLE]) && isset($_POST[Key::POST_CONTENT]) && isset($_POST[Key::POST_PASSWORD]) &&
        strlen($_POST[Key::POST_TITLE]) <= getCharacterMaximumLengthFromSchema(Key::POST_TITLE) &&
        strlen($_POST[Key::POST_PASSWORD]) <= getCharacterMaximumLengthFromSchema(Key::POST_PASSWORD) &&
        isAlreadySignIn();

    if (!$isValidExecCond) {
        throwException(new Exception("잘못 된 실행 조건"));
    }

    $isAttachmentExists = !empty($_FILES[Key::POST_ATTACHMENT]["name"]); //첨부파일 존재 여부
    //echo $isAttachmentExists == true ? "exists" : "not exists";
    //echo var_dump($_FILES[Key::POST_ATTACHMENT]);

    $escapedPostTitle = htmlentities(mysqli_real_escape_string($dbConnection, $_POST[Key::POST_TITLE])); //게시글 제목
    $escapedPostContent = htmlentities(mysqli_real_escape_string($dbConnection, $_POST[Key::POST_CONTENT])); //게시글 내용
    $encPostPassword = password_hash($_POST[Key::POST_PASSWORD], PASSWORD_BCRYPT); //게시글 비밀번호에 대한 암호화된 문자열
    $postAttachmentOriginalFileName = null; //원본 첨부파일명
    $postAttachmentUploadedFileName = null; //업로드 된 첨부파일명

    if ($isAttachmentExists) { //첨부파일 존재 시
        $uploadedFileInfoArray = uploadFile(); //업로드 시도

        if (is_null($uploadedFileInfoArray)) { //첨부파일 업로드 실패 시
            return FAIL;
        }

        $postAttachmentOriginalFileName = $uploadedFileInfoArray[Key::POST_ATTACHMENT_ORIGINAL_FILE_NAME];
        $postAttachmentUploadedFileName = $uploadedFileInfoArray[Key::POST_ATTACHMENT_UPLOADED_FILE_NAME];
    }

    $sql = "INSERT INTO 게시글
            VALUES(null, '{$escapedPostTitle}','{$escapedPostContent}', " .
        ($postAttachmentOriginalFileName == null ? "null, " : "'" . $postAttachmentOriginalFileName . "', ") .
        ($postAttachmentUploadedFileName == null ? "null, " : "'" . $postAttachmentUploadedFileName . "', ") .
        "null, null, '{$encPostPassword}');";

    if ($dbConnection->query($sql)) {
        return SUCCESS;
    } else {
        throwException(new Exception("쿼리 오류 : $sql"));
    }
}

/**
 * 특정 게시글 수정
 * @param int $postNum 특정 게시글 번호
 * @param string $oldPassword 권한 확인을 위한 특정 게시글의 이전 비밀번호 입력 값
 * @return int 작업 상태
 */
function updatePost(int $postNum = -1, string $oldPassword = "")
{
    $dbConnection = getDBConnection();
    $isValidExecCond = $postNum > 0 && $postNum <= PHP_INT_MAX &&
        isset($_POST[Key::POST_TITLE]) && isset($_POST[Key::POST_CONTENT]) && isset($_POST[Key::POST_PASSWORD]) &&
        isset($_POST[Key::POST_ATTACHMENT_MODE]) &&
        strlen($_POST[Key::POST_TITLE]) <= getCharacterMaximumLengthFromSchema(Key::POST_TITLE) &&
        strlen($_POST[Key::POST_PASSWORD]) <= getCharacterMaximumLengthFromSchema(Key::POST_PASSWORD) &&
        isAlreadySignIn() && isPostPasswordMatch($postNum, $oldPassword);

    if (!$isValidExecCond) {
        throwException(new Exception("잘못 된 실행 조건"));
    }

    $isAttachmentExists = !empty($_FILES[Key::POST_ATTACHMENT]["name"]); //첨부파일 존재 여부
    $escapedPostTitle = htmlentities(mysqli_real_escape_string($dbConnection, $_POST[Key::POST_TITLE])); //게시글 제목
    $escapedPostContent = htmlentities(mysqli_real_escape_string($dbConnection, $_POST[Key::POST_CONTENT])); //게시글 내용
    $encPostPassword = password_hash($_POST[Key::POST_PASSWORD], PASSWORD_BCRYPT); //게시글 비밀번호에 대한 암호화된 문자열
    $postAttachmentOriginalFileName = null; //원본 첨부파일명
    $postAttachmentUploadedFileName = null; //업로드 된 첨부파일명
    $oldPostAttachmentUploadedFileName = getSpecificPostUploadedFileName($postNum); //기존에 업로드 된 현재 게시글에 종속 된 첨부파일에 대해 서버에 저장 된 파일 이름

    $isUploadRequire = ($isAttachmentExists && $_POST[Key::POST_ATTACHMENT_MODE] == OVERWRITE_FILE); //첨부파일 업로드 요구 여부
    $isDBAttachmentNameUpdateRequire = false; //DB 상의 첨부파일 원본이름과 저장이름 갱신 요구 여부

    //echo var_dump($_POST) . "<br/>";
    //echo var_dump($_FILES);

    /***
        U) 첨부파일 업로드 요구 여부 (T : 첨부파일 업로드, F : 첨부파일 업로드 수행하지 않음)
        O) 기존에 업로드 된 현재 게시글에 종속 된 첨부파일에 대해 서버에 저장 된 파일 이름 존재 여부 (T : 존재, F : 미 존재)
        D) 이에 따른 수행 작업

        U | O | D
        T   T   기존에 업로드 된 첨부파일 삭제, 새로운 첨부파일 업로드 수행, 이에 따른 DB 상의 첨부파일 원본이름과 저장이름 갱신 요구
        T   F   새로운 첨부파일 업로드 수행, 이에 따른 DB 상의 첨부파일 원본이름과 저장이름 갱신 요구
        F   T   1) 첨부파일 모드가 기존에 업로드 된 파일 삭제일 경우 : 기존에 업로드 된 첨부파일 삭제, 이에 따른 DB 상의 첨부파일 원본이름과 저장이름 null로 갱신 요구
                2) 첨부파일 모드가 기존에 업로드 된 파일 삭제가 아닐 경우 : do nothing
        F   F   do nothing
     ***/

    if ($isUploadRequire) { //첨부파일 업로드 요구
        if (!is_null($oldPostAttachmentUploadedFileName)) { //T T
            deleteFile($oldPostAttachmentUploadedFileName); //기존에 업로드 된 첨부파일 삭제
        }

        //T T or T F
        $uploadedFileInfoArray = uploadFile(); //업로드 시도
        if (is_null($uploadedFileInfoArray)) { //첨부파일 업로드 실패 시
            return FAIL;
        }

        $postAttachmentOriginalFileName = $uploadedFileInfoArray[Key::POST_ATTACHMENT_ORIGINAL_FILE_NAME];
        $postAttachmentUploadedFileName = $uploadedFileInfoArray[Key::POST_ATTACHMENT_UPLOADED_FILE_NAME];
        $isDBAttachmentNameUpdateRequire = true;
    } else { //첨부파일 업로드 수행하지 않음
        if (!is_null($oldPostAttachmentUploadedFileName) && $_POST[Key::POST_ATTACHMENT_MODE] == DELETE_FILE) { //F T 1
            deleteFile($oldPostAttachmentUploadedFileName); //기존에 업로드 된 첨부파일 삭제
            $isDBAttachmentNameUpdateRequire = true;
        }

        //F T 2 or F F : do nothing
    }

    $sql = "UPDATE 게시글
            SET 게시글.글_제목 = '{$escapedPostTitle}', 게시글.글_내용 = '{$escapedPostContent}', " .
        ($isDBAttachmentNameUpdateRequire == true ?
            "게시글.첨부파일_원본이름 = " . ($postAttachmentOriginalFileName == null ? "null, " : "'" . $postAttachmentOriginalFileName . "', ") .
            "게시글.첨부파일_저장이름 = " . ($postAttachmentUploadedFileName == null ? "null, " : "'" . $postAttachmentUploadedFileName . "', ") :
            "") . "게시글.글_비밀번호 = '{$encPostPassword}' WHERE 게시글.글_번호 = {$postNum};";

    if ($dbConnection->query($sql)) {
        return SUCCESS;
    } else {
        throwException(new Exception("쿼리 오류 : $sql"));
    }
}

/**
 * 특정 게시글 삭제
 * @param int $postNum 특정 게시글 번호
 * @return int 작업 상태
 */
function deletePost(int $postNum = -1)
{
    $dbConnection = getDBConnection();
    $isValidExecCond = $postNum > 0 && $postNum <= PHP_INT_MAX &&
        isset($_POST[Key::POST_PASSWORD]) && strlen($_POST[Key::POST_PASSWORD]) <= getCharacterMaximumLengthFromSchema(Key::POST_PASSWORD) &&
        isAlreadySignIn() && isPostPasswordMatch($postNum, $_POST[Key::POST_PASSWORD]);

    if (!$isValidExecCond) {
        throwException(new Exception("잘못 된 실행 조건"));
    }

    $sql = "DELETE FROM 게시글 WHERE 글_번호 = {$postNum};";

    if ($dbConnection->query($sql)) {
        return SUCCESS;
    } else {
        throwException(new Exception("쿼리 오류 : $sql"));
    }


    return FAIL;
}

/**
 * 특정 게시글에 종속 된 댓글 가져오기
 * @param int $replyNum 특정 댓글 번호
 * @return array|null 댓글에 대한 다중 키를 가진 배열 (Key : Key.php에 따름) 혹은 존재하지 않을 시 null 반환
 */
function getSpecificReply(int $replyNum = -1)
{
    $dbConnection = getDBConnection();
    $isValidExecCond = $replyNum > 0 && $replyNum <= PHP_INT_MAX &&
        isAlreadySignIn();

    if (!$isValidExecCond) {
        throwException(new Exception("잘못 된 실행 조건"));
    }

    $retVal = null; //반환 값
    $sql = "SELECT 댓글.댓글_내용
            FROM 댓글
            WHERE 댓글.댓글_번호 = {$replyNum}";

    if ($queryResult = $dbConnection->query($sql)) {
        if ($queryResult->num_rows > 0) { //결과 행 존재 시
            $row = $queryResult->fetch_array(); //결과 행에 대해
            $retVal = array();
            array_push($retVal, $row); //append
        }
    } else {
        throwException(new Exception("쿼리 오류 : $sql"));
    }

    return $retVal;
}

/**
 * 특정 게시글에 종속 된 댓글 생성
 * @param int $postNum 특정 게시글 번호
 * @return int 작업 상태
 */
function createReply(int $postNum = -1)
{
    $dbConnection = getDBConnection();
    $isValidExecCond = $postNum > 0 && $postNum <= PHP_INT_MAX &&
        isset($_POST[Key::REPLY_CONTENT]) && isset($_POST[Key::REPLY_PASSWORD]) &&
        strlen($_POST[Key::REPLY_PASSWORD]) <= getCharacterMaximumLengthFromSchema(Key::REPLY_PASSWORD) &&
        isAlreadySignIn();

    if (!$isValidExecCond) {
        throwException(new Exception("잘못 된 실행 조건"));
    }

    $escapedReplyContent = htmlentities(mysqli_real_escape_string($dbConnection, $_POST[Key::REPLY_CONTENT])); //댓글 내용
    $encReplyPassword = password_hash($_POST[Key::REPLY_PASSWORD], PASSWORD_BCRYPT);
    $sql = "INSERT INTO 댓글 
            VALUES($postNum, null, '{$escapedReplyContent}', null, '{$encReplyPassword}');";

    if ($dbConnection->query($sql)) {
        return SUCCESS;
    } else {
        throwException(new Exception("쿼리 오류 : $sql"));
    }

    return FAIL;
}

/**
 * 특정 게시글에 종속 된 댓글 목록 가져오기
 * @param int $postNum 특정 게시글 번호
 * @param int $replyPageNum 댓글 페이지 번호
 * @return array|null 댓글에 대한 다중 키를 가진 배열 (Key : Key.php에 따름) 혹은 존재하지 않을 시 null 반환
 */
function getReplyList(int $postNum = -1, int $replyPageNum = 1)
{
    $dbConnection = getDBConnection();
    $isValidExecCond = $postNum > 0 && $postNum <= PHP_INT_MAX && $replyPageNum > 0 && $replyPageNum <= PHP_INT_MAX &&
        isAlreadySignIn();

    if (!$isValidExecCond) {
        throwException(new Exception("잘못 된 실행 조건"));
    }

    $retVal = null;
    $startOffset = ($replyPageNum - 1) * PaginationOption::PAGE_PER_REPLY; //페이지 단위 출력을 위한 시작 오프셋
    $sql = "SELECT 댓글.댓글_번호, 댓글.댓글_내용, 댓글.작성일
            FROM 댓글, 게시글
            WHERE 댓글.글_번호 = 게시글.글_번호 AND 게시글.글_번호 = $postNum
            ORDER BY 작성일 DESC, 댓글_번호 DESC
            LIMIT {$startOffset}, " . PaginationOption::PAGE_PER_REPLY . ";"; //시작 오프셋부터 페이지 당 댓글 수만큼 가져옴

    if ($queryResult = $dbConnection->query($sql)) {
        $retVal = array();

        while ($row = $queryResult->fetch_array()) { //결과 행들에 대해
            array_push($retVal, $row); //append
        }
    } else {
        throwException(new Exception("쿼리 오류 : $sql"));
    }

    return $retVal;
}

/**
 * 특정 게시글에 종속 된 댓글 수정
 * @param int $postNum 특정 댓글 번호
 * @param string $oldPassword 권한 확인을 위한 특정 댓글의 이전 비밀번호 입력 값
 * @return int 작업 상태
 */
function updateReply(int $replyNum = -1, string $oldPassword = "")
{
    $dbConnection = getDBConnection();

    $isValidExecCond = $replyNum > 0 && $replyNum <= PHP_INT_MAX &&
        isset($_POST[Key::REPLY_CONTENT]) && isset($_POST[Key::REPLY_PASSWORD]) &&
        strlen($_POST[Key::REPLY_PASSWORD]) <= getCharacterMaximumLengthFromSchema(Key::REPLY_PASSWORD) &&
        isAlreadySignIn() && isReplyPasswordMatch($replyNum, $oldPassword);

    if (!$isValidExecCond) {
        throwException(new Exception("잘못 된 실행 조건"));
    }

    $escapedReplyContent = htmlentities(mysqli_real_escape_string($dbConnection, $_POST[Key::REPLY_CONTENT])); //댓글 내용
    $encReplyPassword = password_hash($_POST[Key::REPLY_PASSWORD], PASSWORD_BCRYPT);

    $sql = "UPDATE 댓글
            SET 댓글.댓글_내용 = '{$escapedReplyContent}', 댓글.댓글_비밀번호 = '{$encReplyPassword}' 
            WHERE 댓글.댓글_번호 = {$replyNum};";

    if ($dbConnection->query($sql)) {
        return SUCCESS;
    } else {
        throwException(new Exception("쿼리 오류 : $sql"));
    }

    return FAIL;
}

/**
 * 특정 게시글에 종속 된 댓글 삭제
 * @param int $replyNum 댓글 번호
 * @return int 작업 상태
 */
function deleteReply(int $replyNum = -1)
{
    $dbConnection = getDBConnection();
    $isValidExecCond = $replyNum > 0 && $replyNum <= PHP_INT_MAX &&
        isset($_POST[Key::REPLY_PASSWORD]) && strlen($_POST[Key::REPLY_PASSWORD]) <= getCharacterMaximumLengthFromSchema(Key::POST_PASSWORD) &&
        isAlreadySignIn() && isReplyPasswordMatch($replyNum, $_POST[Key::REPLY_PASSWORD]);

    if (!$isValidExecCond) {
        throwException(new Exception("잘못 된 실행 조건"));
    }

    $sql = "DELETE FROM 댓글 WHERE 댓글_번호 = {$replyNum};";

    if ($dbConnection->query($sql)) {
        return SUCCESS;
    } else {
        throwException(new Exception("쿼리 오류 : $sql"));
    }

    return FAIL;
}
