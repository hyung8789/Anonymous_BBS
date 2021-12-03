<?php
class Key //키 상수
{
  const SIGN_UP = "signUp"; //회원가입
  const SIGN_IN = "signIn"; //로그인
  const SIGN_OUT = "signOut"; //로그아웃

  const EMAIL = "email"; //이메일
  const PASSWORD = "password"; //비밀번호
  const CONFIRM_PASSWORD = "confirmPassword"; //비밀번호 확인
  const REMEMBER_ME = "remember"; //Remember Me

  const POST_PAGE_NUM = "postPageNum"; //게시글 페이지 번호
  const REPLY_PAGE_NUM = "replyPageNum"; //댓글 페이지 번호

  const NOTHING = "";
  const HOME = "home"; //홈
  const MY_INFO = "myInfo"; //내 정보
  const SEARCH_KEYWORD = "searchKeyword"; //검색어

  const POST_WRITE = "postWrite"; //게시글 작성
  const POST_MODIFY = "postModify"; //게시글 수정
  const POST_DELETE = "postDelete"; //게시글 삭제
  const POST_NUM = "postNum"; //게시글 번호
  const POST_TITLE = "postTitle"; //게시글 제목
  const POST_CONTENT = "postContent"; //게시글 내용
  const POST_ATTACHMENT_ORIGINAL_FILE_NAME = "postAttachmentOriginalFileName"; //게시글 첨부파일에 대한 원본 이름
  const POST_ATTACHMENT_UPLOADED_FILE_NAME = "postAttachmentUploadedFileName"; //게시글 첨부파일에 대한 서버에 저장 된 이름
  const POST_ATTACHMENT = "postAttachment"; //게시글 첨부파일
  const POST_ATTACHMENT_DOWNLOAD = "postAttachmentDownload"; //게시글 첨부파일 다운로드
  const POST_ATTACHMENT_MODE = "postAttachmentMode"; //게시글 첨부파일 모드
  const POST_DATE = "postDate"; //게시글 작성일
  const POST_VIEW_COUNT = "postViewCount"; //게시글 조회수
  const POST_PASSWORD = "postPassword"; //게시글 비밀번호

  const REPLY_WRITE = "replyWrite"; //댓글 작성
  const REPLY_MODIFY = "replyModify"; //댓글 수정
  const REPLY_DELETE = "replyDelete"; //댓글 삭제
  const REPLY_NUM = "replyNum"; //댓글 번호
  const REPLY_CONTENT = "replyContent"; //댓글 내용
  const REPLY_DATE = "replyDate"; //댓글 작성일
  const REPLY_PASSWORD = "replyPassword"; //댓글 비밀번호

  const URL_QUERY = "urlQuery"; //HTTP URL GET 쿼리

  private function __construct()
  {
  }
}
