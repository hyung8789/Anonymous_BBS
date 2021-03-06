/***
	Anonymous BBS Database
***/

DROP DATABASE IF EXISTS bbsDB;
CREATE DATABASE bbsDB;
USE bbsDB;

CREATE TABLE 사용자
(
	이메일 VARCHAR(30) PRIMARY KEY NOT NULL,
	비밀번호 VARCHAR(60) NOT NULL
);

CREATE TABLE 게시글 (
    글_번호 INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    글_제목 VARCHAR(60) NOT NULL,
    글_내용 TEXT NOT NULL,
    첨부파일_원본이름 VARCHAR(256) DEFAULT NULL,
    첨부파일_저장이름 VARCHAR(256) DEFAULT NULL,
    작성일 DATETIME DEFAULT NULL,
    조회수 INT UNSIGNED DEFAULT 0,
    글_비밀번호 VARCHAR(60) NOT NULL
);

CREATE TABLE 댓글
(
	글_번호 INT UNSIGNED,
    댓글_번호 INT UNSIGNED UNIQUE AUTO_INCREMENT,
    댓글_내용 TEXT NOT NULL,
    작성일 DATETIME DEFAULT NULL,
    댓글_비밀번호 VARCHAR(60) NOT NULL,
    
    CONSTRAINT FK_댓글 FOREIGN KEY(글_번호) REFERENCES 게시글(글_번호) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY(글_번호, 댓글_번호)
);

DROP PROCEDURE IF EXISTS 공통_작성일_갱신;
DELIMITER $$
CREATE PROCEDURE 공통_작성일_갱신(INOUT 대상_작성일 DATETIME)
BEGIN
	SET 대상_작성일 = NOW();
END $$
DELIMITER ;

DROP PROCEDURE IF EXISTS 게시글_조회수_증가;
DELIMITER $$
CREATE PROCEDURE 게시글_조회수_증가(IN 대상_글_번호 INT UNSIGNED)
BEGIN
	UPDATE 게시글 SET 조회수 = 조회수 + 1 WHERE 글_번호 = 대상_글_번호; 
END $$
DELIMITER ;

DROP TRIGGER IF EXISTS 게시글_삽입_트리거;
DELIMITER $$
CREATE TRIGGER 게시글_삽입_트리거
BEFORE INSERT ON 게시글
	FOR EACH ROW
BEGIN
	CALL 공통_작성일_갱신(NEW.작성일);
    SET NEW.조회수 = 0;
END $$
DELIMITER ;

DROP TRIGGER IF EXISTS 게시글_갱신_트리거;
DELIMITER $$
CREATE TRIGGER 게시글_갱신_트리거
BEFORE UPDATE ON 게시글
	FOR EACH ROW
BEGIN
	DECLARE 작성일_갱신_필요_여부 bool;
    SET 작성일_갱신_필요_여부 = 
    (OLD.글_제목 <> NEW.글_제목) OR 
    (OLD.글_내용 <> NEW.글_내용) OR 
    (OLD.첨부파일_원본이름 <> NEW.첨부파일_원본이름) OR
    (OLD.첨부파일_저장이름 <> NEW.첨부파일_저장이름);

	IF (작성일_갱신_필요_여부 = true) THEN -- 게시글 클릭에 의해 게시글 조회수 증가 시 본 트리거가 발동되므로 게시글 클릭에 의해 조회수만 변동 된 경우 작성일을 갱신하지 않음
		CALL 공통_작성일_갱신(NEW.작성일);
	END IF;
END $$
DELIMITER ;

DROP TRIGGER IF EXISTS 댓글_삽입_트리거;
DELIMITER $$
CREATE TRIGGER 댓글_삽입_트리거
BEFORE INSERT ON 댓글
	FOR EACH ROW
BEGIN
	CALL 공통_작성일_갱신(NEW.작성일);
END $$
DELIMITER ;

DROP TRIGGER IF EXISTS 댓글_갱신_트리거;
DELIMITER $$
CREATE TRIGGER 댓글_갱신_트리거
BEFORE UPDATE ON 댓글
	FOR EACH ROW
BEGIN
	CALL 공통_작성일_갱신(NEW.작성일);
END $$
DELIMITER ;
