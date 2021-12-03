<?php
date_default_timezone_set("Asia/Seoul"); //서버 시간 대역 설정

ini_set("log_errors", "On"); //오류 로그 기록
ini_set("error_log", __DIR__ . "/../error_logs/error.log"); //오류 로그 출력 경로

// 전역 작업 상태 정의
define("SUCCESS", 1); //성공
define("FAIL", 0); //실패

require_once(__DIR__ . "/controller/exception_controller.php");

require_once(__DIR__ . "/predef/FrontendScript.php");
require_once(__DIR__ . "/predef/Key.php");
require_once(__DIR__ . "/predef/PaginationOption.php");
require_once(__DIR__ . "/predef/AuthType.php");

require_once(__DIR__ . "/manager/SessionManager.php");

require_once(__DIR__ . "/utils/RandGenUtil.php");
require_once(__DIR__ . "/utils/UnitConvertUtil.php");
require_once(__DIR__ . "/utils/UrlQueryUtil.php");

require_once(__DIR__ . "/controller/file_controller.php");
require_once(__DIR__ . "/controller/db_controller.php");
require_once(__DIR__ . "/controller/auth_controller.php");
require_once(__DIR__ . "/controller/board_controller.php");