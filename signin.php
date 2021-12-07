<!-- 로그인 페이지 정의 -->
<?php
require_once(__DIR__ . "/modules/core.php");

if (isAlreadySignIn()) { //이미 로그인되었을 경우
    header("Location: " . FrontendScript::HOME); //메인 페이지로 이동
}
?>

<!DOCTYPE html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="./res/favicon.ico" type="image/x-icon">
    <link rel="icon" href="./res/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="./res/common.css">
    <link rel="stylesheet" type="text/css" href="./res/signin.css">
    <title>로그인</title>
</head>

<body>
    <div id="logoAreaId">
        <img src="./res/logo.png" id="logoImageId">
    </div>
    <!-- 로그인 및 회원가입 수행을 위한 폼 -->
    <?php
    if (isset($_POST[Key::SIGN_IN])) { //로그인에 대해 HTTP POST 명령이 수행되었을 경우
        switch (authProc(AuthType::SIGN_IN)) {
            case SUCCESS:
                header("Location: " . FrontendScript::HOME); //메인 페이지로 이동
                break;
    
            case FAIL:
                echo "<script type='text/javascript'>alert('로그인 실패 : 등록되지 않은 이메일, 잘못 된 비밀번호 입력 혹은 서버 오류');</script>";
                break;
        }
    }
    ?>
    <form method="post">
        <!-- 폼을 위한 요소 그룹 -->
        <fieldset>
            <div id="signInAreaId">
                <p>
                    <label for="<?php echo Key::EMAIL ?>">이메일 주소</label>
                </p>

                <p>
                    <!-- 이메일 입력란 -->
                    <input type="email" placeholder="이메일" maxlength="<?php echo getCharacterMaximumLengthFromSchema(Key::EMAIL) ?>" name="<?php echo Key::EMAIL ?>" autofocus required>
                </p>

                <p>
                    <label for="<?php echo Key::PASSWORD ?>">비밀번호</label>
                </p>

                <p>
                    <!-- 비밀번호 입력란 -->
                    <input type="password" placeholder="비밀번호" maxlength="<?php echo getCharacterMaximumLengthFromSchema(Key::PASSWORD) ?>" name="<?php echo Key::PASSWORD ?>" required>
                </p>

                <p>
                    <input type="checkbox" name="<?php echo Key::REMEMBER_ME?>" name=>
                    <label for="<?php echo Key::REMEMBER_ME?>">Remember me</label>
                </p>

                <hr />

                <p>
                    <input type="submit" name="<?php echo Key::SIGN_IN?>" value="로그인" />
                </p>

                <p>
                    <input type="button" value="회원가입" button onclick="window.open('<?php echo FrontendScript::SIGN_UP ?>','',
                    'width=500,height=400,location=none,status=no,scrollbars=yes')"; />
                </p>
            </div>
        </fieldset>
    </form>
</body>
<br />
<footer>
    <p>Anonymous Bulletin Board System<br />
        <a href="https://github.com/hyung8789">https://github.com/hyung8789</a>
    </p>
</footer>

</html>