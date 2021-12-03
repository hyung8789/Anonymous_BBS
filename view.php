<!-- 게시글 및 댓글에 대한 보기, 작성, 수정, 삭제 공통 페이지 정의 -->
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

$currentPostNum = 1; //현재 게시글 번호
if (isset($_GET[Key::POST_NUM]) && is_numeric($_GET[Key::POST_NUM])) { //HTTP GET 요청에 게시글 번호가 있을 경우
    $currentPostNum = (int)$_GET[Key::POST_NUM];
} else {
    header("Location: " . FrontendScript::HOME); //메인 페이지로 이동
}

$currentReplyPageNum = 1; //현재 댓글 페이지 번호
$totalReplyPageCount = getTotalReplyPageCount($currentPostNum); //현재 게시글에 대한 전체 댓글 페이지 수
if (array_key_exists(Key::REPLY_PAGE_NUM, $currentUrlQueryArray) && is_numeric($currentUrlQueryArray[Key::REPLY_PAGE_NUM])) { //HTTP GET 요청에 댓글 페이지 번호가 있을 경우
    if ($totalReplyPageCount < (int)$currentUrlQueryArray[Key::REPLY_PAGE_NUM] || (int)$currentUrlQueryArray[Key::REPLY_PAGE_NUM] < 1) { //범위 초과 시
        $currentReplyPageNum = 1;
    } else { //해당 요청으로 페이지 번호 할당
        $currentReplyPageNum = (int)$currentUrlQueryArray[Key::REPLY_PAGE_NUM];
    }
}

$currentEditorMode = null; //에디터 모드 (댓글 작성, 수정)
$currentReplyNum = null; //댓글 수정을 위한 현재 댓글 번호
$oldReplyContent = null; //이전 게시글 내용
if (array_key_exists(Key::REPLY_MODIFY, $currentUrlQueryArray) && array_key_exists(Key::REPLY_NUM, $currentUrlQueryArray)) { //댓글 수정
    $currentReplyNum = $currentUrlQueryArray[Key::REPLY_NUM];
    $currentEditorMode = Key::REPLY_MODIFY;
} else { //댓글 작성 모드
    $currentEditorMode = Key::REPLY_WRITE;
}
?>
<!DOCTYPE html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="./res/favicon.ico" type="image/x-icon">
    <link rel="icon" href="./res/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="./res/common.css">
    <link rel="stylesheet" type="text/css" href="./res/navigation.css">
    <link rel="stylesheet" type="text/css" href="./res/boardview.css">
    <link rel="stylesheet" type="text/css" href="./res/pagination.css">
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
    <!-- 게시글 출력 테이블 -->
    <table class="boardView">
        <!-- 테이블 상단 -->
        <thead>
            <!-- 테이블에 대한 헤더 셀 출력 -->
            <tr>
                <?php
                $postArray = getSpecificPost($currentPostNum); //게시글 번호에 해당되는 게시글 배열
                if ($postArray != null) { //게시글이 존재하면
                    increasePostViewCount($currentPostNum); //해당 게시글 조회수 증가

                    $row = $postArray[0]; //단일 행 결과
                    $postTitleBuffer = "<th class='" . Key::POST_TITLE . "'>{$row["글_제목"]}" .
                        ($row["첨부파일_저장이름"] == null ?
                            "" : " <img src='./res/attachment.png' class='" . Key::POST_ATTACHMENT . "'>") .
                        " [" . $row["댓글_수"] . "] " . "</th>";

                    $thBuffer = "
                    {$postTitleBuffer}
                    <th colspan='3' class='" . Key::POST_DATE . "'>작성일 : {$row["작성일"]}</th>";
                    echo $thBuffer; //제목 헤더 셀 출력

                } else {
                    echo "<th class='postNothingToShowMessage'>존재하지 않거나 삭제 된 게시글입니다.</th></tr>";
                    throwException(new Exception("존재하지 않거나 삭제 된 게시글 접근"));
                }
                ?>
            </tr>
        </thead>

        <!-- 테이블 몸통 -->
        <tbody>
            <!-- 게시글 내용 출력 -->
            <tr>
                <?php
                $postContent = nl2br($row["글_내용"]); //개행문자에 대해 처리 된 게시글 내용
                echo "<td colspan='4' class='" . Key::POST_CONTENT . "'> {$postContent}</td>";
                ?>
            </tr>

            <!-- 게시글에 대한 첨부파일 및 버튼 출력 -->
            <tr>
                <td>
                    <?php
                    if (array_key_exists(Key::POST_ATTACHMENT_DOWNLOAD, $currentUrlQueryArray)) { //게시글 첨부파일 다운로드에 대해 HTTP GET 명령이 수행되었을 경우
                        downloadFile($row["첨부파일_원본이름"], $row["첨부파일_저장이름"]);
                    }

                    echo "<img src='./res/attachment.png' class='" . Key::POST_ATTACHMENT . "'>" .
                        ($row["첨부파일_저장이름"] == null ?
                            " <b>첨부 된 파일이 존재하지 않습니다.</b>" :
                            " <b><a href='" . UrlQueryUtil::genUrlIncMergeQuery($_SERVER["SCRIPT_NAME"], $currentUrlQueryArray, array(Key::POST_ATTACHMENT_DOWNLOAD => Key::NOTHING)) . "'>" .
                            $row["첨부파일_원본이름"] . " (" . UnitConvertUtil::bytesToShorthandNotation(getUploadedFileSize($row["첨부파일_저장이름"]), "m", 2)  . "b)</a></b>");
                    ?>
                </td>

                <td class="buttonArea">
                    <input type="button" button onclick='location.href="<?php echo UrlQueryUtil::genUrlIncMergeQuery($_SERVER["SCRIPT_NAME"], $currentUrlQueryArray, array(Key::HOME => Key::NOTHING)) ?>"' value="목록" />

                </td>

                <td class="buttonArea">
                    <input type="button" button onclick="window.open('<?php echo UrlQueryUtil::genUrlIncQuery(FrontendScript::CONFIRM_PASSWORD, array(Key::POST_MODIFY => Key::NOTHING, Key::POST_NUM => $currentPostNum)) ?>','',
                    'width=500,height=200,location=none,status=no,scrollbars=yes')" ; value="수정" />
                </td>

                <td class="buttonArea">
                    <input type="button" button onclick="window.open('<?php echo UrlQueryUtil::genUrlIncQuery(FrontendScript::CONFIRM_PASSWORD, array(Key::POST_DELETE => Key::NOTHING, Key::POST_NUM => $currentPostNum)) ?>','',
                    'width=500,height=200,location=none,status=no,scrollbars=yes')" ; value="삭제" />
                </td>
            </tr>
        </tbody>

        <!-- 테이블 하단 -->
        <tfoot>
        </tfoot>
    </table>

    <br />

    <!-- 댓글 작성 폼 -->
    <?php
    switch ($currentEditorMode) { //현재 에디터 모드에 따라
        case Key::REPLY_WRITE:
            if (isset($_POST[Key::REPLY_WRITE])) { //댓글 작성에 대해 HTTP POST 명령이 수행되었을 경우
                switch (createReply($currentPostNum)) {
                    case SUCCESS:
                        break;

                    case FAIL:
                        echo "<script type='text/javascript'>alert('댓글 작성 실패 : 잘못 된 입력 값 혹은 서버 오류');</script>";
                        break;
                }
            }

            break;

        case Key::REPLY_MODIFY:
            //echo var_dump($_SESSION);
            if (isset($_POST[Key::REPLY_MODIFY])) { //댓글 수정에 대해 HTTP POST 명령이 수행되었을 경우
                switch (updateReply($currentUrlQueryArray[Key::REPLY_NUM], SessionManager::getSessionDataAndDestroy(Key::REPLY_PASSWORD))) {
                    case SUCCESS:
                        //ex : view.php?postNum=1&replyModify=&replyNum=1 에서 postNum 을 제외 한 모든 쿼리 제거
                        $newQueryArray = UrlQueryUtil::genUrlQueryArrayExcludeDiffFromKey(
                            $currentUrlQueryArray,
                            array(Key::REPLY_MODIFY => Key::NOTHING, Key::REPLY_NUM => Key::NOTHING)
                        ); //새 쿼리 배열

                        $redirectionTargetLocation = UrlQueryUtil::genUrlIncQuery($_SERVER["SCRIPT_NAME"], $newQueryArray);
                        header("Location: " . $redirectionTargetLocation); //불필요한 GET 쿼리를 제거한 주소로 리디렉션
                        break;

                    case FAIL:
                        echo "<script type='text/javascript'>alert('댓글 수정 오류 : 올바르지 않은 비밀번호 혹은 서버 오류');</script>";
                        break;
                }
            } else { //댓글 수정
                $isValidExecCond = isset($currentUrlQueryArray[Key::REPLY_NUM]) &&
                    isReplyPasswordMatch($currentUrlQueryArray[Key::REPLY_NUM], SessionManager::getSessionData(Key::REPLY_PASSWORD));

                if (!$isValidExecCond) {
                    echo "<script type='text/javascript'>alert('댓글 수정 오류 : 올바르지 않은 비밀번호 혹은 서버 오류'); window.history.back();</script>"; //이전 페이지로 이동
                    throwException(new Exception("댓글 수정 오류 : 올바르지 않은 비밀번호 혹은 서버 오류"));
                }

                $oldReplyArray = getSpecificReply($currentUrlQueryArray[Key::REPLY_NUM]); //댓글 번호에 해당되는 이전 댓글 배열

                if ($oldReplyArray != null) { //댓글이 존재하면
                    $row = $oldReplyArray[0]; //단일 행 결과
                    $oldReplyContent = $row["댓글_내용"]; //이전 댓글 내용

                } else {
                    echo "<th class='postNothingToShowMessage'>존재하지 않거나 삭제 된 댓글입니다.</th></tr>";
                    throwException(new Exception("존재하지 않거나 삭제 된 댓글 접근"));
                }
            }

            break;
    }

    ?>
    <form name="replyForm" method="post">
        <table class="replyFormTable">
            <!-- 테이블 상단 -->
            <thead>
            </thead>

            <!-- 테이블 몸통 -->
            <tbody>
                <tr>
                    <td colspan="2"> <label for="<?php echo Key::REPLY_CONTENT ?>">댓글 작성</label></td>
                </tr>

                <tr>
                    <td colspan="2">
                        <textarea id="<?php echo Key::REPLY_CONTENT ?>" name="<?php echo Key::REPLY_CONTENT ?>" title="내용" rows="5" placeholder="내용을 입력하세요." required><?php echo $oldReplyContent ?></textarea>
                    </td>
                </tr>

                <tr class="buttonArea">
                    <td>
                    </td>

                    <td>
                        <label for="<?php echo Key::REPLY_PASSWORD ?>">댓글 비밀번호 (최대 <?php echo getCharacterMaximumLengthFromSchema(Key::REPLY_PASSWORD) ?>자)</label><em>*</em>
                        <input type="password" placeholder="댓글 비밀번호 (최대 <?php echo getCharacterMaximumLengthFromSchema(Key::REPLY_PASSWORD) ?>자)" maxlength="<?php echo getCharacterMaximumLengthFromSchema(Key::REPLY_PASSWORD) ?>" name="<?php echo Key::REPLY_PASSWORD ?>" required>
                        <input type="submit" name="<?php echo $currentEditorMode ?>" value="댓글 작성" />
                    </td>
                </tr>
            </tbody>

            <!-- 테이블 하단 -->
            <tfoot>
            </tfoot>
        </table>
    </form>
    <br />
    <!-- 댓글 출력 테이블 -->
    <table class="boardView">
        <!-- 테이블 상단 -->
        <thead>
            <!-- 테이블에 대한 헤더 셀 출력 -->
            <tr>
                <th class="<?php echo Key::REPLY_NUM ?>">댓글 번호</th>
                <th class="<?php echo Key::REPLY_CONTENT ?>">댓글 내용</th>
                <th class="<?php echo Key::REPLY_DATE ?>">댓글 작성일</th>
                <th></th>
            </tr>
        </thead>

        <!-- 테이블 몸통 -->
        <tbody>
            <?php
            $replyListArray = getReplyList($currentPostNum, $currentReplyPageNum); //현재 게시글의 댓글 페이지 번호에 대한 댓글 목록 배열

            //echo var_dump($replyListArray);
            if (count($replyListArray) >= 1) { //댓글 목록이 존재하면
                foreach ($replyListArray as $row) { //결과의 각 행에 대해
                    $buttonBuffer = "
                <td class='buttonArea'>
                    <input type='button' button onclick=" . '"window.open(' . "'" .
                        UrlQueryUtil::genUrlIncQuery(FrontendScript::CONFIRM_PASSWORD, array(Key::REPLY_MODIFY => Key::NOTHING, Key::REPLY_NUM => $row["댓글_번호"])) .
                        "','','width=500,height=200,location=none,status=no,scrollbars=yes')" . '";' . " value='수정' />" .
                        "<input type='button' button onclick=" . '"window.open(' . "'" .
                        UrlQueryUtil::genUrlIncQuery(FrontendScript::CONFIRM_PASSWORD, array(Key::REPLY_DELETE => Key::NOTHING, Key::REPLY_NUM => $row["댓글_번호"])) .
                        "','','width=500,height=200,location=none,status=no,scrollbars=yes')" . '";' . " value='삭제' />" .
                        "</td>"; //버튼 출력 버퍼

                    $replyContent = nl2br($row['댓글_내용']); //개행문자에 대해 처리 된 댓글 내용
                    $rowBuffer = "
                    <tr>
                        <td class='" . Key::REPLY_NUM . "'>{$row["댓글_번호"]}</td>
                        <td class='" . Key::REPLY_CONTENT . "'>{$replyContent}</td>
                        <td class='" . Key::REPLY_DATE . "'>{$row["작성일"]}</td>
                        {$buttonBuffer}
                    </tr>";

                    echo $rowBuffer; //한 행 출력
                }
            } else {
                echo "<tr><td class='replyNothingToShowMessage' colspan='3'>등록 된 댓글이 존재하지 않습니다.</td></tr>";
            }
            ?>

        </tbody>

        <!-- 테이블 하단 -->
        <tfoot>
            <!-- 댓글에 대한 페이징 영역 -->
            <tr class='pagingArea'>
                <td colspan='4'>
                    <?php
                    $startPageNum = max($currentReplyPageNum - PaginationOption::PAGE_PER_PAGE_NUM_COUNT, 1); //시작 페이지 번호
                    $endPageNum = min($currentReplyPageNum + PaginationOption::PAGE_PER_PAGE_NUM_COUNT, $totalReplyPageCount); //끝 페이지 번호
                    $prevPageNum = max($currentReplyPageNum - 1, 1); //이전 페이지 번호
                    $nextPageNum = min($currentReplyPageNum + 1, $totalReplyPageCount); //다음 페이지 번호

                    $firstLink = UrlQueryUtil::genUrlIncMergeQuery($_SERVER["SCRIPT_NAME"], $currentUrlQueryArray, array(Key::POST_NUM => $currentPostNum, Key::REPLY_PAGE_NUM => $startPageNum)); //처음
                    $lastLink = UrlQueryUtil::genUrlIncMergeQuery($_SERVER["SCRIPT_NAME"], $currentUrlQueryArray, array(Key::POST_NUM => $currentPostNum, Key::REPLY_PAGE_NUM => $totalReplyPageCount)); //끝
                    $prevLink = UrlQueryUtil::genUrlIncMergeQuery($_SERVER["SCRIPT_NAME"], $currentUrlQueryArray, array(Key::POST_NUM => $currentPostNum, Key::REPLY_PAGE_NUM => $prevPageNum)); //이전
                    $nextLink = UrlQueryUtil::genUrlIncMergeQuery($_SERVER["SCRIPT_NAME"], $currentUrlQueryArray, array(Key::POST_NUM => $currentPostNum, Key::REPLY_PAGE_NUM => $nextPageNum)); //다음

                    echo "<a class='pageMove' href='{$firstLink}'><< 처음 </a>";
                    echo "<a class='pageMove' href='{$prevLink}'>< 이전 </a>";

                    for ($i = $startPageNum; $i <= $endPageNum; $i++) { //현재 페이지 기준 출력이 시작되는 시작 페이지부터 끝 페이지 번호까지
                        $indexLink = UrlQueryUtil::genUrlIncMergeQuery($_SERVER["SCRIPT_NAME"], $currentUrlQueryArray, array(Key::POST_NUM => $currentPostNum, Key::REPLY_PAGE_NUM => $i));

                        if ($i == $currentReplyPageNum) { //현재 페이지의 경우
                            echo "<a class='pageNum current' href='{$indexLink}'>[{$i}]</a>";
                        } else {
                            echo "<a class='pageNum' href='{$indexLink}'>[{$i}]</a>";
                        }
                    }

                    echo "<a class='pageMove' href='{$nextLink}'> 다음 ></a>";
                    echo "<a class='pageMove' href='{$lastLink}'> 끝 >></a>";
                    ?>

                </td>
            </tr>
        </tfoot>
    </table>
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