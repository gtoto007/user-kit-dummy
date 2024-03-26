<?php

namespace Toto\UserKit\DTOs;

class Paginator
{

    public function __construct(public int $page, public int $per_page, public int $total_pages, public int $total, public array $data)
    {

    }
}