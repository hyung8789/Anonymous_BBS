<?php
class PaginationOption //Pagination을 위한 옵션 상수
{ 
    const PAGE_PER_POST = 30; //페이지 당 게시글 수
    const PAGE_PER_REPLY = 10; //페이지 당 댓글 수
    const PAGE_PER_PAGE_NUM_COUNT = 4; //페이지 당 페이지 번호 개수 (현재 페이지 기준 양 쪽으로 각각 출력되는 페이지 번호 개수)

    private function __construct()
    {
    }
}