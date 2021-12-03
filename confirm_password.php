<!-- 게시글, 댓글에 대해 수정, 삭제를 위한 공통 비밀번호 확인 페이지 정의 -->
<?php
require_once(__DIR__ . "/modules/core.php");

if (!isAlreadySignIn()) { //로그인되지 않았을 경우
    header("Location: " . FrontendScript::SIGN_IN); //로그인 페이지로 이동
}

if(!SessionManager::isSessionDataAllocated(Key::URL_QUERY)){
    SessionManager::setSessionData(
        Key::URL_QUERY,
        UrlQueryUtil::getUrlQueryArray($_SERVER["HTTP_REFERER"]),
        true
    ); //호출 한 부모 측의 URL 쿼리 배열 저장
}

$currentUrlQueryArray = UrlQueryUtil::getCallerUrlQueryArray(); //현재 URL 쿼리 배열

$confirmPasswordTargetKey = null; //비밀번호 확인 대상 키 (게시글 수정, 삭제 혹은 댓글 수정, 삭제)
$transferTargetKey = null; //비밀번호 확인 대상에 따른 전송 대상 키 (폼 양식으로 입력받을 데이터를 위한 키)

if (array_key_exists(Key::POST_MODIFY, $currentUrlQueryArray) && array_key_exists(Key::POST_NUM, $currentUrlQueryArray)) { //게시글 수정
    $confirmPasswordTargetKey = Key::POST_MODIFY;
    $transferTargetKey = Key::POST_PASSWORD;
} else if (array_key_exists(Key::POST_DELETE, $currentUrlQueryArray) && array_key_exists(Key::POST_NUM, $currentUrlQueryArray)) { //게시글 삭제
    $confirmPasswordTargetKey = Key::POST_DELETE;
    $transferTargetKey = Key::POST_PASSWORD;
} else if (array_key_exists(Key::REPLY_MODIFY, $currentUrlQueryArray) && array_key_exists(Key::REPLY_NUM, $currentUrlQueryArray)) { //댓글 수정
    $confirmPasswordTargetKey = Key::REPLY_MODIFY;
    $transferTargetKey = Key::REPLY_PASSWORD;
} else if (array_key_exists(Key::REPLY_DELETE, $currentUrlQueryArray) && array_key_exists(Key::REPLY_NUM, $currentUrlQueryArray)) { //댓글 삭제
    $confirmPasswordTargetKey = Key::REPLY_DELETE;
    $transferTargetKey = Key::REPLY_PASSWORD;
} else {
    throwException(new Exception("적절하지 않은 요청"));
    echo "<script type='text/javascript'>window.close();</script>";
}
?>

<!DOCTYPE html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="./res/favicon.ico" type="image/x-icon">
    <link rel="icon" href="./res/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="./res/common.css">
    <title>비밀번호 확인</title>
</head>

<body>
    <!-- 비밀번호 확인 폼 -->
    <?php
    if (isset($_POST[$confirmPasswordTargetKey]) && isset($_POST[$transferTargetKey])) { //비밀번호 확인 대상과 비밀번호 확인 대상에 따른 전송 대상 키가 할당되었으면
        switch ($confirmPasswordTargetKey) { //비밀번호 확인 대상 키에 따라 적절한 요청인지 판별 및 수행
            case Key::POST_MODIFY:
                $parentTargetLocation = UrlQueryUtil::genUrlIncQuery(
                    FrontendScript::EDITOR,
                    $currentUrlQueryArray
                ); //부모 창의 대상 경로

                SessionManager::destroySessionData(Key::POST_PASSWORD);
                SessionManager::setSessionData(
                    Key::POST_PASSWORD,
                    $_POST[Key::POST_PASSWORD],
                    true
                ); //폼 양식 데이터를 부모 창으로 전송 위해 임시로 저장

                // https://stackoverflow.com/questions/11590535/change-parent-window-location-from-popup
                echo "<script type='text/javascript'>opener.location.href = '{$parentTargetLocation}';</script>";
                break;

            case Key::POST_DELETE:
                switch (deletePost($currentUrlQueryArray[Key::POST_NUM])) {
                    case SUCCESS:
                        $parentTargetLocation = FrontendScript::HOME; //부모 창의 대상 경로
                        echo "<script type='text/javascript'>alert('게시글 삭제 성공');</script>";
                        echo "<script type='text/javascript'>opener.location.href = '{$parentTargetLocation}';</script>";
                        break;

                    case FAIL:
                        echo "<script type='text/javascript'>alert('게시글 삭제 실패 : 올바르지 않은 비밀번호 혹은 서버 오류');</script>";
                        break;
                }
                break;

            case Key::REPLY_MODIFY:
                //echo var_dump(SessionDataUtil::getSessionData(Key::URL_QUERY));
                $parentTargetLocation = UrlQueryUtil::genUrlIncMergeQuery(
                    FrontendScript::VIEW,
                    SessionManager::getSessionDataAndDestroy(Key::URL_QUERY),
                    $currentUrlQueryArray
                ); //부모 창의 대상 경로

                SessionManager::destroySessionData(Key::REPLY_PASSWORD);
                SessionManager::setSessionData(
                    Key::REPLY_PASSWORD,
                    $_POST[Key::REPLY_PASSWORD],
                    true
                ); //폼 양식 데이터를 부모 창으로 전송 위해 임시로 저장

                echo "<script type='text/javascript'>opener.location.href = '{$parentTargetLocation}';</script>";
                break;


            case Key::REPLY_DELETE:
                switch (deleteReply($currentUrlQueryArray[Key::REPLY_NUM])) {
                    case SUCCESS:
                        echo "<script type='text/javascript'>alert('댓글 삭제 성공');</script>";
                        echo "<script type='text/javascript'>opener.location.reload(true);</script>"; //부모 창 새로고침
                        break;

                    case FAIL:
                        echo "<script type='text/javascript'>alert('댓글 삭제 실패 : 올바르지 않은 비밀번호 혹은 서버 오류');</script>";
                        break;
                }
                break;


            default:
                throwException(new Exception("적절하지 않은 요청"));
        }

        echo "<script type='text/javascript'>window.close();</script>";
    }
    ?>

    <form name="confirmPasswordForm" method="post">
        <div id="confirmPasswordAreaId">
            <p>
                <!-- 비밀번호 입력 확인란 -->
                <label for="<?php echo $transferTargetKey ?>">비밀번호 확인 (최대 <?php echo getCharacterMaximumLengthFromSchema($transferTargetKey) ?>자)</label><em>*</em>
            </p>
            <p>
                <input type="password" placeholder="비밀번호 확인" maxlength="<?php echo getCharacterMaximumLengthFromSchema($transferTargetKey) ?>" name="<?php echo $transferTargetKey ?>" required>
            </p>
        </div>
        <hr />
        <p><input type="submit" name="<?php echo $confirmPasswordTargetKey ?>" value="확인" /></p>
        <p><input type="button" value="취소" button onclick="window.close();" /></p>
    </form>
    </tbody>
    </table>
</body>

<script>
    // https://developer.mozilla.org/en-US/docs/Web/API/History/replaceState
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href); //페이지 새로고침 시 양식 다시 제출 방지
    }
</script>

</html>