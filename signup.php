<!-- 회원가입 페이지 정의 -->
<?php
require_once(__DIR__ . "/modules/core.php");
?>

<!DOCTYPE html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="./res/favicon.ico" type="image/x-icon">
    <link rel="icon" href="./res/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="./res/common.css">
    <title>회원가입</title>
</head>

<body>
    <!-- 회원가입 폼 -->
    <?php
    if (isset($_POST[Key::SIGN_UP])) { //회원가입에 대해 HTTP POST 명령이 수행되었을 경우
        switch (authProc(AuthType::SIGN_UP)) { //회원가입 수행
            case SUCCESS:
                echo "<script type='text/javascript'>alert('회원가입 성공');
                window.close();</script>";
                break;

            case FAIL:
                echo "<script type='text/javascript'>alert('회원가입 실패 : 이미 존재하는 이메일, 잘못 된 비밀번호 입력 혹은 서버 오류');</script>";
                break;
        }
    }
    ?>
    <form name="signUpForm" method="post">
        <div id="signUpAreaId">
            <p>
                <label for="<?php echo Key::EMAIL ?>">이메일 주소 (최대 <?php echo getCharacterMaximumLengthFromSchema(Key::EMAIL) ?>자)</label><em>*</em>
            </p>
            <p>
                <!-- 이메일 입력란 -->
                <input type="email" placeholder="이메일" maxlength="<?php echo getCharacterMaximumLengthFromSchema(Key::EMAIL) ?>" name="<?php echo Key::EMAIL ?>" autofocus required>
            </p>

            <p>
                <label for="<?php echo Key::PASSWORD ?>">비밀번호 (최대 <?php echo getCharacterMaximumLengthFromSchema(Key::PASSWORD) ?>자)</label><em>*</em>
            </p>

            <p>
                <!-- 비밀번호 입력란 -->
                <input type="password" placeholder="비밀번호" maxlength="<?php echo getCharacterMaximumLengthFromSchema(Key::PASSWORD) ?>" name="<?php echo Key::PASSWORD ?>" required>
            </p>

            <p>
                <!-- 비밀번호 입력 확인란 -->
                <label for="<?php echo Key::CONFIRM_PASSWORD ?>">비밀번호 확인 (최대 <?php echo getCharacterMaximumLengthFromSchema(Key::PASSWORD) ?>자)</label><em>*</em>
            </p>
            <p>
                <input type="password" placeholder="비밀번호 확인" maxlength="<?php echo getCharacterMaximumLengthFromSchema(Key::PASSWORD) ?>" name="<?php echo Key::CONFIRM_PASSWORD ?>" required>
            </p>
        </div>
        <hr />
        <p><input type="submit" name="<?php echo Key::SIGN_UP ?>" value="회원가입" /></p>
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