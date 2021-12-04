# Anonymous_BBS
Anonymous Bulletin Board System

working

---

회원가입, 로그인

게시글, 댓글 CRUD 작업

게시글에 대한 첨부파일 업로드, 다운로드

글 목록 및 댓글에 대한 페이지네이션 및 작성일 기준 최신순으로 출력 (전체 목록 및 검색 결과에 대하여, 글 및 댓글 목록 분할하여 출력)

글 옆에 댓글 수 및 첨부파일 여부 출력

게시글 클릭 시 조회수 카운트 (중복 카운트 방지)

게시글의 제목 및 내용에 대하여 검색 기능

글 삭제 시 해당 글과 관련 된 댓글도 DB에서 제거

사용자 입력 값에 대한 유효성 검증

보안 (게시글, 댓글, 검색 기능에 대한 XSS, Sql 인젝션 방지 및 첨부파일에 대한 제한, 기타 보안 사항 추가) 

---

TODO

관리자 페이지 기능 구현

비밀번호 변경 및 탈퇴 기능을 포함한 내 정보 페이지 구현

test driver에 대해 별도의 콜백 함수 델리게이트를 통한 호출 (호출 한 함수 이름 등 추가 정보 출력)

SQL 쿼리에 대해 prepared statement 이용

SQL Injection Test
https://www.google.com/search?q=php+sql+injection+test&client=ms-android-samsung-ss&sxsrf=AOaemvIWZYBTPTHy99tBNKaXcHMu95s2SQ%3A1638511186776&ei=UrKpYZvrLouLr7wPgbe0qAQ&ved=0ahUKEwib3P3p-cb0AhWLxYsBHYEbDUUQ4dUDCA4&uact=5&oq=php+sql+injection+test&gs_lcp=Cgdnd3Mtd2l6EAMyBQgAEIAEMgYIABAIEB4yBggAEAgQHjIGCAAQCBAeMggIABAIEAoQHjoHCAAQRxCwAzoGCAAQBxAeOggIABAIEAcQHjoKCAAQCBAHEAoQHkoECEEYAFAdWLsDYOoMaAFwAngAgAGYAYgBkQKSAQMwLjKYAQCgAQHIAQjAAQE&sclient=gws-wiz

CSRF Attack 방어를 위해 confirm password에서 http Referer 체크 및 추가 보안사항 확인
https://kciter.so/posts/basic-web-hacking

manager 및 controller에 대해 구조 리팩토링

모든 폴더에 대해 디렉토리 리스팅방지 및 php 직접 호출 방지 (recursive htaccess)
https://stackoverflow.com/questions/409496/prevent-direct-access-to-a-php-include-file

세션 및 쿠키에 대한 보안처리
https://phppot.com/php/secure-remember-me-for-login-using-php-session-and-cookies/
https://www.google.com/search?q=php+cookie+auth+security&ei=AGipYZneE43Z-Qa-9YDADg&ved=0ahUKEwiZ3rv5ssb0AhWNbN4KHb46AOgQ4dUDCA4&uact=5&oq=php+cookie+auth+security&gs_lcp=Cgdnd3Mtd2l6EAMyBQgAEM0CMgUIABDNAjIFCAAQzQIyBQgAEM0COgkIABCwAxAIEB5KBAhBGAFQwhJYsxhghhloAXAAeAGAAecBiAGMBpIBBTEuNC4xmAEAoAEByAECwAEB&sclient=gws-wiz
https://www.php.net/manual/en/features.session.security.management.php
https://www.php.net/manual/en/function.session-regenerate-id.php

세션과 관련 된 작업 모두 세션 관리자로 통합

자동 회원가입 방지를 위해 Captcha 인증 추가

세션 이용 동시에 다수의 요청 거부, DDOS 방어

사용자 인증에 대한 보안 (세션과 쿠키를 통한 이메일 대조가 아닌 사용자 인증을 위한 세션을 별도로 관리 할 것)
https://stackoverflow.com/questions/549/the-definitive-guide-to-form-based-website-authentication#477579
https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence

프론트 단의 중복되는 공통 영역 (GET쿼리에 대한 처리, 네비게이션, 푸터 영역)에 대해서 별도의 파일로 분리 할 것
