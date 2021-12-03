<?php

define("POST_ATTACHMENT_PATH", __DIR__ . "/../../uploaded_files/"); //첨부파일 업로드 경로

// 파일 처리를 위한 요청 타입 정의
define("IN_BYTES_SIZE", 0); //바이트 단위 크기
define("IN_SHORTHAND_NOTATION_SIZE", 1); //약식 표기 된 크기
define("OVERWRITE_FILE", 10); //기존에 업로드 된 파일 Overwrite
define("DELETE_FILE", 11); //기존에 업로드 된 파일 제거 (파일 업로드란 무시)

/**
 * 파일 처리를 위한 요청 타입에 따라 업로드 가능 한 최대 파일 크기 반환
 * @param int $requestTypeForFile 파일 처리를 위한 요청 타입
 * @return int|string 업로드 가능 한 최대 파일 크기
 */
function getUploadMaxFileSize(int $requestTypeForFile = 0)
{
    // https://www.php.net/manual/en/features.file-upload.common-pitfalls.php
    // https://www.php.net/manual/en/ini.core.php#ini.post-max-size
    // https://www.php.net/manual/en/ini.core.php#ini.memory-limit

    static $uploadMaxFileSizeInBytes = null; //바이트 단위 업로드 제한 크기

    if (!isset($uploadMaxFileSizeInBytes)) {
        $tmpArray = array(
            "phpUploadMaxFileSize" => UnitConvertUtil::shorthandNotationToBytes(ini_get("upload_max_filesize")), //최대 파일 당 업로드 제한 크기
            "phpPostMaxSize" => UnitConvertUtil::shorthandNotationToBytes(ini_get("post_max_size")), //HTTP POST 요청에 대한 최대 크기
            "phpMemoryLimit" => UnitConvertUtil::shorthandNotationToBytes(ini_get("memory_limit")) //서버 측의 스크립트에 대한 물리적 메모리 할당 제한 크기
        );

        //echo var_dump($tmpArray);

        $uploadMaxFileSizeInBytes = min($tmpArray);
        unset($tmpArray);
    }

    switch ($requestTypeForFile) {
        case IN_BYTES_SIZE:
            return $uploadMaxFileSizeInBytes;

        case IN_SHORTHAND_NOTATION_SIZE:
            return UnitConvertUtil::bytesToShorthandNotation($uploadMaxFileSizeInBytes, "M"); //MB 단위

        default:
            throwException(new Exception("잘못 된 요청 타입"));
    }
}

/**
 * 파일 업로드 처리
 * @return array|null 업로드 된 원본 파일명과 임시 파일명을 포함한 배열 (Key : Key.php에 따름) 혹은 파일 업로드 실패 시 null 반환
 */
function uploadFile()
{
    // https://www.php.net/manual/en/reserved.variables.files
    // https://www.php.net/manual/en/features.file-upload.post-method.php

    $isValidExecCond = isAlreadySignIn();

    if (!$isValidExecCond) {
        throwException(new Exception("잘못 된 실행 조건"));
    }

    $retVal = null;
    $uploadFileName = date("YmdHis") . "_" . RandGenUtil::genRandString(6); //게시글 첨부파일에 대한 서버에 저장 될 이름 (현재 날짜 및 시간_임의의 문자열)
    $uploadFullPath = POST_ATTACHMENT_PATH . $uploadFileName; //전체 경로

    if (is_uploaded_file($_FILES[Key::POST_ATTACHMENT]["tmp_name"]) &&
        move_uploaded_file($_FILES[Key::POST_ATTACHMENT]["tmp_name"], $uploadFullPath)) { //HTTP POST를 통하여 업로드 된 파일인 경우, 저장 경로로 이동

        $retVal = array(
            Key::POST_ATTACHMENT_ORIGINAL_FILE_NAME => $_FILES[Key::POST_ATTACHMENT]["name"], //게시글 첨부파일에 대한 원본 이름
            Key::POST_ATTACHMENT_UPLOADED_FILE_NAME => $uploadFileName //서버에 저장 된 파일 이름
        );
    } else {
        throwException(new Exception("파일 업로드 실패"), false);
    }

    return $retVal;
}

/**
 * 업로드 된 파일의 바이트 단위 크기 반환
 * @param string $uploadedFileName 서버에 저장 된 파일 이름
 * @return int 업로드 된 파일의 바이트 단위 크기
 */
function getUploadedFileSize(string $uploadedFileName)
{
    $isValidExecCond = isset($uploadedFileName) && isAlreadySignIn();

    if (!$isValidExecCond) {
        throwException(new Exception("잘못 된 실행 조건"));
    }

    $uploadedFullPath = POST_ATTACHMENT_PATH . $uploadedFileName; //전체 경로

    if (file_exists($uploadedFullPath)) {
        $uploadedFileSizeInBytes = filesize($uploadedFullPath);

        if ($uploadedFileSizeInBytes !== false) {
            return $uploadedFileSizeInBytes;
        } else {
            throwException(new Exception("파일 크기 획득 실패"));
        }
    } else {
        throwException(new Exception("게시글 첨부파일에 대한 서버에 저장 된 이름이 존재하지 않음"));
    }
}

/**
 * 업로드 된 파일 다운로드
 * @param string $originalFileName 서버에 저장 된 파일의 원본 이름
 * @param string $uploadedFileName 서버에 저장 된 파일 이름
 * @return void 
 */
function downloadFile(string $originalFileName, string $uploadedFileName)
{
    // https://www.php.net/manual/en/function.readfile.php
    // https://stackoverflow.com/questions/13311790/php-readfile-causing-corrupt-file-downloads

    $isValidExecCond = isset($originalFileName) && isset($uploadedFileName) &&
        isAlreadySignIn();

    if (!$isValidExecCond) {
        throwException(new Exception("잘못 된 실행 조건"));
    }

    $uploadedFullPath = POST_ATTACHMENT_PATH . $uploadedFileName; //전체 경로

    if (file_exists($uploadedFullPath)) {
        // https://developer.mozilla.org/ko/docs/Web/HTTP/Headers
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=" . basename($originalFileName));
        header("Expires: 0");
        header("Cache-Control: must-revalidate");
        header("Pragma: public");
        header("Content-Length: " . getUploadedFileSize($uploadedFileName));

        while (ob_get_level()) { //모든 출력 버퍼 비우기
            ob_end_clean();
        }

        readfile($uploadedFullPath); //출력 버퍼로 파일 내용 출력
        flush();
        exit(); //종료하지 않을 경우 브라우저에서 출력 위해 파일 뒤에 html문서 내용이 들어감

    } else {
        throwException(new Exception("게시글 첨부파일이 존재하지 않음"));
    }
}

/**
 * 업로드 된 파일 삭제
 * @param string $uploadedFileName 서버에 저장 된 파일 이름
 * @return void 
 */
function deleteFile(string $uploadedFileName)
{
    $isValidExecCond = isset($uploadedFileName) && isAlreadySignIn();

    if (!$isValidExecCond) {
        throwException(new Exception("잘못 된 실행 조건"));
    }

    $uploadedFullPath = POST_ATTACHMENT_PATH . $uploadedFileName; //전체 경로

    if (file_exists($uploadedFullPath)) {
        if (!unlink($uploadedFullPath)) { //삭제 시도
            throwException(new Exception("게시글 첨부파일 삭제 실패 : $uploadedFullPath"));
        }
    } else {
        throwException(new Exception("게시글 첨부파일이 존재하지 않음"));
    }
}