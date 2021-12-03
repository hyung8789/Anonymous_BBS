<?php

/**
 * 예외 던지기
 * @param Exception $ex 예외
 * @param bool $terminate 치명적 오류로 인한 스크립트 종료 여부 (true : 스크립트 종료 (default), false : 계속 수행)
 * @return void 
 */
function throwException(Exception $ex, bool $terminate = true)
{
    $errorMessage = 
    "예외 발생 파일 : " . $ex->getFile() . "\n" .
    "예외 내용 : " . $ex->getMessage(). "\n" .
    "Trace : " . $ex->getTraceAsString();
    
    error_log($errorMessage, 0);

    if($terminate)
       exit(FAIL);
}