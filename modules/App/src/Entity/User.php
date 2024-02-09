<?php

namespace App\Entity;

class User implements \JsonSerializable
{
    private int $id;

    private string $name;

    private string $email;

    public function __construct(string $name, string $email)
    {
        $this->name = $name;
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }


    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
