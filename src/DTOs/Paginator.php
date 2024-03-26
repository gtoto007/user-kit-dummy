<?php

namespace Toto\UserKit\DTOs;

class Paginator
{
    public const DEFAULT_PAGE = 1;
    public const DEFAULT_PER_PAGE = 6;

    public function __construct(public int $page, public int $per_page, public int $total_pages, public int $total, public array $data)
    {

    }
}