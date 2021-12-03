<?php

class FrontendScript //프로젝트 루트 디렉토리 기준 프론트 단 스크립트
{
    const SIGN_IN = "/signin.php"; //로그인 페이지
    const SIGN_UP = "/signup.php"; //회원가입 페이지

    const HOME = "/main.php"; //홈 화면 (메인 페이지)
    const MY_INFO = "/my_info.php"; //내 정보 페이지

    const EDITOR = "/editor.php"; //게시글 작성, 수정 공통 페이지
    const VIEW = "/view.php"; //게시글 및 댓글에 대한 보기, 작성, 수정, 삭제 공통 페이지
    const CONFIRM_PASSWORD = "/confirm_password.php"; //게시글, 댓글에 대해 수정, 삭제를 위한 공통 비밀번호 확인 페이지

    private function __construct()
    {
    }
}
