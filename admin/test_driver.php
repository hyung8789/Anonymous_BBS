<?php
require_once(__DIR__ . "/../modules/core.php");

// UrlQueryUtil 테스트
echo UrlQueryUtil::getCallerFileName() . "<br/>";
echo var_dump(UrlQueryUtil::getCallerUrlQueryArray()) . "<br/>";
$currentFileName = UrlQueryUtil::getCallerFileName();
$targetUrl = $currentFileName; //대상 URL
$oldGetQueryArray = UrlQueryUtil::getCallerUrlQueryArray(); //현재 GET 쿼리
$newGetQueryArray = UrlQueryUtil::genUrlQueryArray(["param1" => 3], ["param2" => 4]);
echo "Current Get Query : " . UrlQueryUtil::genUrlIncQuery($targetUrl, $newGetQueryArray) . "<br/>";
echo "Merge Get Query : " . UrlQueryUtil::genUrlIncMergeQuery($targetUrl, $oldGetQueryArray, $newGetQueryArray) . "<br/>";
$excQueryArray = UrlQueryUtil::genUrlQueryArrayExcludeDiffFromKey($newGetQueryArray, array("param1" => Key::NOTHING));
echo var_dump($excQueryArray) . "<br/>";

echo "<hr/>";
// UnitConvertUtil 테스트
echo UnitConvertUtil::shorthandNotationToBytes("1M") . "<br/>";
echo UnitConvertUtil::bytesToShorthandNotation(UnitConvertUtil::shorthandNotationToBytes("1M"), "k") . "<br/>"; //KB 변환

echo "<hr/>";
// file_controller 테스트
echo getUploadMaxFileSize(IN_BYTES_SIZE). "<br/>";
echo getUploadMaxFileSize(IN_SHORTHAND_NOTATION_SIZE). "<br/>";

// exception_handler 테스트
//throwException(new Exception("test exception"));

echo "<hr/>";
// 현재 날짜 출력 테스트
echo date("YmdHis") . "<br/>";

//세션 데이터 유틸리티 테스트
echo "<hr/>";
SessionManager::setSessionData("test", 1, true);
SessionManager::setSessionData("test", 2, true);
echo SessionManager::getSessionData("test") . "<br/>";
echo SessionManager::getSessionDataAndDestroy("test"). "<br/>";
SessionManager::setSessionData("test", 3, true);
echo SessionManager::getSessionDataAndDestroy("test"). "<br/>";