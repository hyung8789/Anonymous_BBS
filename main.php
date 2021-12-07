<!-- 게시글 출력, 검색 기능을 포함한 메인 페이지 정의 -->
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

$searchKeyword = ""; //검색어
if (array_key_exists(Key::SEARCH_KEYWORD, $currentUrlQueryArray)) { //HTTP GET 요청에 검색어가 있을 경우
    $searchKeyword = (string)$currentUrlQueryArray[Key::SEARCH_KEYWORD];
}

$currentPostPageNum = 1; //현재 게시글 페이지 번호
$totalPostPageCount = getTotalPostPageCount($searchKeyword); //게시글에 대한 전체 페이지 수
if (array_key_exists(Key::POST_PAGE_NUM, $currentUrlQueryArray) && is_numeric($currentUrlQueryArray[Key::POST_PAGE_NUM])) { //HTTP GET 요청에 게시글 페이지 번호가 있을 경우
    if ($totalPostPageCount < (int)$currentUrlQueryArray[Key::POST_PAGE_NUM] || (int)$currentUrlQueryArray[Key::POST_PAGE_NUM] < 1) { //범위 초과 시
        $currentPostPageNum = 1;
    } else { //해당 요청으로 페이지 번호 할당
        $currentPostPageNum = (int)$currentUrlQueryArray[Key::POST_PAGE_NUM];
    }
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
    <!-- 게시판 영역 -->
    <table id="boardList">
        <!-- 테이블 상단 -->
        <thead>
            <!-- 테이블에 대한 헤더 셀 출력 -->
            <tr>
                <th class="<?php echo Key::POST_NUM ?>">글 번호</th>
                <th class="<?php echo Key::POST_TITLE ?>">제목</th>
                <th class="<?php echo Key::POST_DATE ?>">작성일</th>
                <th class="<?php echo Key::POST_VIEW_COUNT ?>">조회수</th>
            </tr>
        </thead>

        <!-- 테이블 몸통 -->
        <tbody>
            <?php
            $postListArray = getPostList($currentPostPageNum, $searchKeyword); //현재 게시글 페이지 번호에 대한 게시글 목록 배열

            if (count($postListArray) >= 1) { //게시글 목록이 존재하면
                foreach ($postListArray as $row) { //결과의 각 행에 대해
                    $postLink = UrlQueryUtil::genUrlIncQuery(FrontendScript::VIEW, array(Key::POST_NUM => $row["글_번호"]));
                    $postTitleBuffer = "<td class='" . Key::POST_TITLE . "' " .
                        "onclick=location.href='" . $postLink . "'>
                        {$row["글_제목"]}" . ($row["첨부파일_존재여부"] ?
                            " <img src='./res/attachment.png' class='" . Key::POST_ATTACHMENT . "'>" :
                            "") .
                        " [" . $row["댓글_수"] . "] " . "</td>";

                    $trBuffer = "
                    <tr id='postList'>
                        <td class='" . Key::POST_NUM . "'>{$row["글_번호"]}</td>
                        {$postTitleBuffer}
                        <td class='" . Key::POST_DATE . "'>{$row["작성일"]}</td>
                        <td class='" . Key::POST_VIEW_COUNT . "'>{$row["조회수"]}</td>
                    </tr>";

                    echo $trBuffer; //한 행 출력
                }
            } else {
                echo "<tr><td class='postNothingToShowMessage' colspan='4'>등록 된 게시글이 존재하지 않습니다.</td></tr>";
            }
            ?>
        </tbody>

        <!-- 테이블 하단 -->
        <tfoot>
            <!-- 게시글에 대한 페이징 영역 -->
            <tr class="pagingArea">
                <td></td>
                <td colspan="2">
                    <?php
                    /***
                        현재 페이지 기준 출력이 시작되는 시작, 끝, 이전, 다음 페이지 번호 지정
                        ---
                        1) 시작 페이지 번호 : 최소 (1), 최대 (현재 페이지 번호 - 페이지 당 페이지 개수)
                        => 현재 페이지 기준 시작 페이지 번호 지정 시 둘 중 최대 값으로 할당

                        2) 끝 페이지 번호 : 최소 (현재 페이지 번호 + 페이지 당 페이지 개수), 최대 (전체 페이지 수)
                        => 현재 페이지 기준 끝 페이지 번호 지정 시 둘 중 최소 값으로 할당

                        3) 이전 페이지 번호 : 최소 (1), 최대 (현재 페이지 번호 - 이전 페이지로 이동 위해 1 감소)
                        => 둘 중 최대 값으로 할당

                        4) 다음 페이지 번호 : 최소 (현재 페이지 번호 + 다음 페이지로 이동 위해 1 증가), 최대 (전체 페이지 수)
                        => 둘 중 최소 값으로 할당
                     ***/

                    $startPageNum = max($currentPostPageNum - PaginationOption::PAGE_PER_PAGE_NUM_COUNT, 1); //시작 페이지 번호
                    $endPageNum = min($currentPostPageNum + PaginationOption::PAGE_PER_PAGE_NUM_COUNT, $totalPostPageCount); //끝 페이지 번호
                    $prevPageNum = max($currentPostPageNum - 1, 1); //이전 페이지 번호
                    $nextPageNum = min($currentPostPageNum + 1, $totalPostPageCount); //다음 페이지 번호

                    $firstLink = UrlQueryUtil::genUrlIncMergeQuery($_SERVER["SCRIPT_NAME"], $currentUrlQueryArray, array(Key::POST_PAGE_NUM => $startPageNum)); //처음
                    $lastLink = UrlQueryUtil::genUrlIncMergeQuery($_SERVER["SCRIPT_NAME"], $currentUrlQueryArray, array(Key::POST_PAGE_NUM => $totalPostPageCount)); //끝
                    $prevLink = UrlQueryUtil::genUrlIncMergeQuery($_SERVER["SCRIPT_NAME"], $currentUrlQueryArray, array(Key::POST_PAGE_NUM => $prevPageNum)); //이전
                    $nextLink = UrlQueryUtil::genUrlIncMergeQuery($_SERVER["SCRIPT_NAME"], $currentUrlQueryArray, array(Key::POST_PAGE_NUM => $nextPageNum)); //다음

                    echo "<a class='pageMove' href='{$firstLink}'><< 처음 </a>";
                    echo "<a class='pageMove' href='{$prevLink}'>< 이전 </a>";

                    for ($i = $startPageNum; $i <= $endPageNum; $i++) { //현재 페이지 기준 출력이 시작되는 시작 페이지부터 끝 페이지 번호까지
                        $indexLink = UrlQueryUtil::genUrlIncMergeQuery($_SERVER["SCRIPT_NAME"], $currentUrlQueryArray, array(Key::POST_PAGE_NUM => $i));

                        if ($i == $currentPostPageNum) { //현재 페이지의 경우
                            echo "<a class='pageNum current' href='{$indexLink}'>[{$i}]</a>";
                        } else {
                            echo "<a class='pageNum' href='{$indexLink}'>[{$i}]</a>";
                        }
                    }

                    echo "<a class='pageMove' href='{$nextLink}'> 다음 ></a>";
                    echo "<a class='pageMove' href='{$lastLink}'> 끝 >></a>";
                    ?>
                </td>
                <!-- 게시글 작성 버튼 출력 -->
                <td class="buttonArea"><input type="button" onclick='location.href="<?php echo FrontendScript::EDITOR ?>"' value="게시글 작성" /></td>
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

</html>