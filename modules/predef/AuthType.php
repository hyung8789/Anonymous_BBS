<?php
class AuthType //인증 타입
{
    const SIGN_IN = 0; //로그인에 대한 인증 타입
    const SIGN_UP = 1; //회원가입에 대한 인증 타입
    const SIGN_OUT = 2; //로그아웃에 대한 인증 타입
    //const SESSION = 3; //세션에 대한 인증 타입
    //const COOKIE = 4; //쿠키에 대한 인증 타입

    private function __construct()
    {
    }
}