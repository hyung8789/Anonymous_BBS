<!-- 게시글 작성, 수정 공통 페이지 정의 -->
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

$currentEditorMode = null; //에디터 모드 (게시글 작성, 수정)
$currentPostNum = null; //게시글 수정을 위한 현재 게시글 번호
$oldPostTitle = null; //이전 게시글 제목
$oldPostContent = null; //이전 게시글 내용
if (array_key_exists(Key::POST_MODIFY, $currentUrlQueryArray) && array_key_exists(Key::POST_NUM, $currentUrlQueryArray)) { //게시글 수정
    $currentPostNum = $currentUrlQueryArray[Key::POST_NUM];
    $currentEditorMode = Key::POST_MODIFY;
} else { //게시글 작성 모드
    $currentEditorMode = Key::POST_WRITE;
}
?>
<!DOCTYPE html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="./res/favicon.ico" type="image/x-icon">
    <link rel="icon" href="./res/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="./res/common.css">
    <link rel="stylesheet" type="text/css" href="./res/navigation.css">
    <link rel="stylesheet" type="text/css" href="./res/editor.css">
    <title>게시글 작성</title>
</head>
<header>
    <!-- 상단 네비게이션 영역 -->
    <nav>
        <a class="current" href="<?php echo UrlQueryUtil::genUrlIncMergeQuery($_SERVER["SCRIPT_NAME"], $currentUrlQueryArray, array(Key::HOME => Key::NOTHING)) ?>">홈</a>
        <a href="<?php echo UrlQueryUtil::genUrlIncMergeQuery($_SERVER["SCRIPT_NAME"], $currentUrlQueryArray, array(Key::MY_INFO => Key::NOTHING)) ?>">내 정보</a>
        <a href="<?php echo UrlQueryUtil::genUrlIncMergeQuery($_SERVER["SCRIPT_NAME"], $currentUrlQueryArray, array(Key::SIGN_OUT => Key::NOTHING)) ?>">로그아웃</a>
        <form name="searchForm" action="<?php echo FrontendScript::HOME ?>" method="get">
            <input type="search" autocomplete="on" name="<?php echo Key::SEARCH_KEYWORD ?>" placeholder="검색어 입력" required />
            <input type="submit" value="검색" />
        </form>
    </nav>
</header>

<body>
    <br />
    <!-- 게시글 작성 폼 -->
    <?php
    switch ($currentEditorMode) { //현재 에디터 모드에 따라
        case Key::POST_WRITE:
            if (isset($_POST[Key::POST_WRITE])) { //게시글 작성에 대해 HTTP POST 명령이 수행되었을 경우
                switch (createPost()) {
                    case SUCCESS:
                        header("Location: " . FrontendScript::HOME); //메인 페이지로 이동
                        break;

                    case FAIL:
                        echo "<script type='text/javascript'>alert('게시글 작성 실패 : 잘못 된 입력 값, 첨부파일 용량 초과 혹은 서버 오류');</script>";
                        break;
                }
            }

            break;

        case Key::POST_MODIFY:
            if (isset($_POST[Key::POST_MODIFY])) { //게시글 수정에 대해 HTTP POST 명령이 수행되었을 경우
                switch (updatePost($currentUrlQueryArray[Key::POST_NUM], SessionManager::getSessionDataAndDestroy(Key::POST_PASSWORD))) {
                    case SUCCESS:
                        header("Location: " . FrontendScript::HOME); //메인 페이지로 이동
                        break;

                    case FAIL:
                        echo "<script type='text/javascript'>alert('게시글 수정 실패 : 잘못 된 입력 값, 첨부파일 용량 초과 혹은 서버 오류');</script>";
                        break;
                }
            } else { //게시글 수정
                $isValidExecCond =  isset($currentUrlQueryArray[Key::POST_NUM]) &&
                    isPostPasswordMatch($currentUrlQueryArray[Key::POST_NUM], SessionManager::getSessionData(Key::POST_PASSWORD));

                if (!$isValidExecCond) {
                    echo "<script type='text/javascript'>alert('게시글 수정 오류 : 올바르지 않은 비밀번호 혹은 서버 오류');window.history.back();</script>"; //이전 페이지로 이동
                    throwException(new Exception("게시글 수정 오류 : 올바르지 않은 비밀번호 혹은 서버 오류"));
                }

                $oldPostArray = getSpecificPost($currentUrlQueryArray[Key::POST_NUM]); //게시글 번호에 해당되는 이전 게시글 배열

                if ($oldPostArray != null) { //게시글이 존재하면
                    $row = $oldPostArray[0]; //단일 행 결과
                    $oldPostTitle = $row["글_제목"]; //이전 게시글 제목
                    $oldPostContent = $row["글_내용"]; //이전 게시글 내용

                } else {
                    echo "<th class='postNothingToShowMessage'>존재하지 않거나 삭제 된 게시글입니다.</th></tr>";
                    throwException(new Exception("존재하지 않거나 삭제 된 게시글 접근"));
                }
            }

            break;
    }
    ?>
    <form name="editorForm" method="post" enctype="multipart/form-data">
        <table id="editor" cellpadding="5px">
            <!-- 테이블 상단 -->
            <thead>
                <tr>
                    <td>
                        <input type="text" id="<?php echo Key::POST_TITLE ?>" name="<?php echo Key::POST_TITLE ?>" maxlength="<?php echo getCharacterMaximumLengthFromSchema(Key::POST_TITLE) ?>" placeholder="제목을 입력하세요. (최대 <?php echo getCharacterMaximumLengthFromSchema(Key::POST_TITLE) ?>자)" value="<?php echo $oldPostTitle ?>" required />
                    </td>
                </tr>
            </thead>

            <!-- 테이블 몸통 -->
            <tbody>
                <tr>
                    <td>
                        <textarea id="<?php echo Key::POST_CONTENT ?>" name="<?php echo Key::POST_CONTENT ?>" title="내용" rows="40" placeholder="내용을 입력하세요." required><?php echo $oldPostContent ?></textarea>
                    </td>
                </tr>
            </tbody>

            <!-- 테이블 하단 -->
            <tfoot>
                <!-- 파일 업로드 및 버튼 출력란 -->
                <tr>
                    <td>
                        <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo getUploadMaxFileSize(IN_BYTES_SIZE) ?>" />
                        <label for="<?php echo Key::POST_ATTACHMENT ?>">파일 업로드 (최대 업로드 제한 크기 : <?php echo getUploadMaxFileSize(IN_SHORTHAND_NOTATION_SIZE) . "B" ?>) :</label>
                        <input type="file" id="<?php echo Key::POST_ATTACHMENT ?>" name="<?php echo Key::POST_ATTACHMENT ?>">
                    </td>
                </tr>

                <?php
                if ($currentEditorMode == Key::POST_MODIFY) { //게시글 수정일 경우
                    echo "
                        <tr>
                            <td class='radioGroupArea'>
                                <input type='radio' name='" . Key::POST_ATTACHMENT_MODE .
                        "' id='" . OVERWRITE_FILE . "'" . "' value='" . OVERWRITE_FILE . "' checked>
                                <label for='" . OVERWRITE_FILE . "'>기존에 업로드 된 파일이 존재 할 경우 덮어씌우기</label>
                            </td>
                        </tr>

                        <tr>
                            <td class='radioGroupArea'>
                                <input type='radio' name='" . Key::POST_ATTACHMENT_MODE .
                        "' id='" . DELETE_FILE . "' value='" . DELETE_FILE . "'>
                                <label for='" . DELETE_FILE . "'>기존에 업로드 된 파일이 존재 할 경우 삭제 (파일 업로드란 무시)</label>
                            </td>
                        </tr>";
                }
                ?>

                <!-- 게시글 비밀번호 및 버튼 출력란 -->
                <tr>
                    <td class="passwordArea">
                        <label for="<?php echo Key::POST_PASSWORD ?>">게시글 비밀번호 (최대 <?php echo getCharacterMaximumLengthFromSchema(Key::POST_PASSWORD) ?>자)</label><em>*</em>
                        <input type="password" placeholder="게시글 비밀번호 (최대 <?php echo getCharacterMaximumLengthFromSchema(Key::POST_PASSWORD) ?>자)" maxlength="<?php echo getCharacterMaximumLengthFromSchema(Key::POST_PASSWORD) ?>" name="<?php echo Key::POST_PASSWORD ?>" required>
                        <input type="submit" name="<?php echo $currentEditorMode ?>" value="게시글 작성" />
                        <input type="button" onclick='location.href="<?php echo UrlQueryUtil::genUrlIncMergeQuery($_SERVER["SCRIPT_NAME"], $currentUrlQueryArray, array(Key::HOME => Key::NOTHING)) ?>"' value="취소" />
                    </td>
                </tr>

            </tfoot>
        </table>
    </form>
</body>
<br />
<footer>
    <p>Anonymous Bulletin Board System<br />
        <a href="https://github.com/hyung8789">https://github.com/hyung8789</a>
    </p>
</footer>

<script>
    // https://developer.mozilla.org/en-US/docs/Web/API/History/replaceState
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href); //페이지 새로고침 시 양식 다시 제출 방지
    }
</script>

</html>