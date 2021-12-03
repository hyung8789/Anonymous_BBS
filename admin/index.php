<?php
define("ADMIN_MODE", true); //bypass auth
require_once(__DIR__ . "/../modules/core.php");

function debugCreatePost()
{
    for ($i = 0; $i < (int)$_GET["count"]; $i++) {
        $_POST[Key::POST_TITLE] = "디버그용 게시글 제목{$i}";
        $_POST[Key::POST_CONTENT] = "디버그용 게시글 내용{$i}";
        $_POST[Key::POST_PASSWORD] = $_GET["password"];
        createPost();
    }

    echo 'done';
}

function debugCreateReply()
{
    for ($i = 0; $i < (int)$_GET["count"]; $i++) {
        $_POST[Key::REPLY_CONTENT] = "디버그용 댓글 내용{$i}";
        $_POST[Key::REPLY_PASSWORD] = $_GET["password"];
        createReply((int)$_GET["target"]);
    }

    echo 'done';
}

function debugDeletePost()
{
    $_POST[Key::POST_PASSWORD] = "admin";

    if (deletePost((int)$_GET["target"]) == SUCCESS) {
        echo 'done';
    }
}

function debugDeleteReply()
{
    $_POST[Key::REPLY_PASSWORD] = "admin";

    if (deleteReply((int)$_GET["target"]) == SUCCESS) {
        echo 'done';
    }
}

function debugClearUser()
{
    $dbConnection = getDBConnection();
    $sql = "TRUNCATE TABLE 사용자;";
    
    if ($queryResult = $dbConnection->query($sql)) {
        echo 'done';
    } else {
        throwException(new Exception("쿼리 오류 : $sql"));
    }
}

function debugClearPost()
{
    $dbConnection = getDBConnection();
    // https://www.techonthenet.com/mysql/auto_increment.php;
    $sql = "ALTER TABLE 댓글 DROP CONSTRAINT FK_댓글;
            TRUNCATE TABLE 게시글;
            TRUNCATE TABLE 댓글;
            ALTER TABLE 게시글 AUTO_INCREMENT = 1;
            ALTER TABLE 댓글 ADD CONSTRAINT FK_댓글 FOREIGN KEY(글_번호) REFERENCES 게시글(글_번호) ON DELETE CASCADE ON UPDATE CASCADE;";
    
    if ($dbConnection->multi_query($sql)) {
        echo 'done';
    } else {
        throwException(new Exception("쿼리 오류 : $sql"));
    }
}

function debugClearReply()
{
    $dbConnection = getDBConnection();
    $sql = "TRUNCATE TABLE 댓글;
            ALTER TABLE 댓글 AUTO_INCREMENT = 1;";
    
    if ($dbConnection->multi_query($sql)) {
        echo 'done';
    } else {
        throwException(new Exception("쿼리 오류 : $sql"));
    }
}

function debugTrimUploadedFiles()
{
    //DB와 일치하지 않는 파일들에 대해 모두 제거
}

function debugXSSTest()
{
    $payload = <<< EOD
    <SCRIPT SRC=http://xss.rocks/xss.js></SCRIPT>
    EOD;

    $_POST[Key::POST_TITLE] = $payload;
    $_POST[Key::POST_CONTENT] = $payload;
    $_POST[Key::POST_PASSWORD] = "1234";
    createPost();

    $_POST[Key::REPLY_CONTENT] = $payload;
    $_POST[Key::REPLY_PASSWORD] = "1234";
    createReply(1);
}

$clearUserUrl = UrlQueryUtil::genUrlIncQuery(UrlQueryUtil::getCallerFileName(), array("clearUser" => Key::NOTHING));
$clearPostUrl = UrlQueryUtil::genUrlIncQuery(UrlQueryUtil::getCallerFileName(), array("clearPost" => Key::NOTHING));
$clearReplyUrl = UrlQueryUtil::genUrlIncQuery(UrlQueryUtil::getCallerFileName(), array("clearReply" => Key::NOTHING));
$trimUploadedFiles = "";
$xssTestUrl = UrlQueryUtil::genUrlIncQuery(UrlQueryUtil::getCallerFileName(), array("xssTest" => Key::NOTHING));

$message = <<< EOD
<b>< 관리자 페이지 ></b>
<form name="adminForm1" method="get">
<b>임의의 개수만큼 게시글 생성</b>
<input type="text" id="count" name="count" placeholder="생성 할 개수" required />
<input type="text" id="password" name="password" placeholder="비밀번호" required/>
<input type="submit" id="genPost" name="genPost" value="생성" />
</form>

<form name="adminForm2" method="get">
<b>대상 게시글 번호에 해당되는 게시글에 임의의 개수만큼 댓글 생성</b>
<input type="text" id="target" name="target" placeholder="대상 게시글 번호" required/>
<input type="text" id="count" name="count" placeholder="생성 할 개수" required/>
<input type="text" id="password" name="password" placeholder="비밀번호" required/>
<input type="submit" id="genReply" name="genReply" value="생성" />
</form>

<form name="adminForm3" method="get">
<b>대상 게시글 번호에 해당되는 게시글 삭제</b>
<input type="text" id="target" name="target" placeholder="대상 게시글 번호" required/>
<input type="submit" id="delPost" name="delPost" value="삭제" />
</form>

<form name="adminForm4" method="get">
<b>대상 댓글 번호에 해당되는 댓글 삭제</b>
<input type="text" id="target" name="target" placeholder="대상 댓글 번호" required/>
<input type="submit" id="delReply" name="delReply" value="삭제" />
</form>

<a href="$clearUserUrl">모든 사용자 초기화</a>
<a href="$clearPostUrl">모든 게시글 초기화 (연관 된 댓글도 모두 초기화)</a>
<a href="$clearReplyUrl">모든 댓글 초기화</a>

<a href="./">DB와 일치하지 않는 uploaded_files 내 파일 제거</a>

<a href="./test_driver.php">모듈 테스트</a>
<a href="$xssTestUrl">XSS 테스트</a>
<a href="./">SQL Injection 테스트</a>

<a href="./phpinfo.php">goto phpinfo</a>
<hr/>
EOD;

echo nl2br($message);

$currentUrlQueryArray = UrlQueryUtil::getCallerUrlQueryArray();
if (array_key_exists("genPost", $currentUrlQueryArray)) {
    debugCreatePost();
} else if (array_key_exists("genReply", $currentUrlQueryArray)) {
    debugCreateReply();
} else if (array_key_exists("delPost", $currentUrlQueryArray)) {
    debugDeletePost();
} else if (array_key_exists("delReply", $currentUrlQueryArray)) {
    debugDeleteReply();
} else if (array_key_exists("clearUser", $currentUrlQueryArray)) {
    debugClearUser();
} else if (array_key_exists("clearPost", $currentUrlQueryArray)) {
    debugClearPost();
} else if (array_key_exists("clearReply", $currentUrlQueryArray)) {
    debugClearReply();
} else if(array_key_exists("xssTest", $currentUrlQueryArray)){
    debugXSSTest();
}
else {
    echo "Ready";
}
