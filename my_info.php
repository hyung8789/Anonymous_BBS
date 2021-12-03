<!-- 내 정보 페이지 정의 -->
<?php
require_once(__DIR__ . "/modules/core.php");

if (!isAlreadySignIn()) { //로그인되지 않았을 경우
    header("Location: " . FrontendScript::SIGN_IN); //로그인 페이지로 이동
}

$currentUrlQueryArray = UrlQueryUtil::getCallerUrlQueryArray(); //현재 URL 쿼리 배열
if (array_key_exists(Key::HOME, $currentUrlQueryArray)) { //홈 이동에 대해 HTTP GET 명령이 수행되었을 경우
    header("Location: " . FrontendScript::HOME); //메인 페이지로 이동
} else if (array_key_exists(Key::MY_INFO, $currentUrlQueryArray)) { //내 정보에 대해 HTTP GET 명령이 수행되었을 경우
    header("Location: " . FrontendScript::MY_INFO); //내 정보 페이지로 이동
} else if (array_key_exists(Key::SIGN_OUT, $currentUrlQueryArray)) { //로그아웃에 대해 HTTP GET 명령이 수행되었을 경우
    authProc(AuthType::SIGN_OUT);
    header("Location: " . FrontendScript::SIGN_IN); //로그인 페이지로 이동
}
?>
<!DOCTYPE html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="./res/favicon.ico" type="image/x-icon">
    <link rel="icon" href="./res/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="./res/common.css">
    <link rel="stylesheet" type="text/css" href="./res/navigation.css">
    <link rel="stylesheet" type="text/css" href="./res/boardlist.css">
    <link rel="stylesheet" type="text/css" href="./res/pagination.css">
    <title>HOME</title>
</head>
<header>
    <!-- 상단 네비게이션 영역 -->
    <nav>
        <a href="<?php echo UrlQueryUtil::genUrlIncMergeQuery($_SERVER["SCRIPT_NAME"], $currentUrlQueryArray, array(Key::HOME => Key::NOTHING)) ?>">홈</a>
        <a class="current" href="<?php echo UrlQueryUtil::genUrlIncMergeQuery($_SERVER["SCRIPT_NAME"], $currentUrlQueryArray, array(Key::MY_INFO => Key::NOTHING)) ?>">내 정보</a>
        <a href="<?php echo UrlQueryUtil::genUrlIncMergeQuery($_SERVER["SCRIPT_NAME"], $currentUrlQueryArray, array(Key::SIGN_OUT => Key::NOTHING)) ?>">로그아웃</a>
        <form name="searchForm" action="<?php echo FrontendScript::HOME ?>" method="get">
            <input type="search" autocomplete="on" name="<?php echo Key::SEARCH_KEYWORD ?>" placeholder="검색어 입력" required />
            <input type="submit" value="검색" />
        </form>
    </nav>
</header>

<body>
<p>비밀번호 변경, 탈퇴 추가</p>
</body>
<br />
<footer>
    <p>Anonymous Bulletin Board System<br />
        <a href="https://github.com/hyung8789">https://github.com/hyung8789</a>
    </p>
</footer>
</html>