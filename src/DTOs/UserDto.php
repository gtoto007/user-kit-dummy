<?php

namespace Toto\UserKit\DTOs;

use JsonSerializable;
use Toto\UserKit\Interfaces\ArraySerializable;

class UserDto implements JsonSerializable, ArraySerializable
{

    public function __construct(public ?int $id, public string $email, public string $first_name, public string $last_name, public $avatar = "")
    {

    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        $output = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email'=>$this->email,
            'avatar' => $this->avatar];

        if ($this->id !== null) {
            $output['id'] = $this->id;
        }
        return $output;
    }
}