<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\Types\Type;
use MongoDB\BSON\ObjectId;

/**
 * @see https://www.doctrine-project.org/projects/doctrine-mongodb-bundle/en/5.0/index.html
 */
#[ODM\Document]
class Product implements \JsonSerializable
{
    #[ODM\Id(type: Type::STRING)]
    protected string $id;

    #[ODM\Field(type: Type::STRING)]
    protected string $name;

    #[ODM\Field(type: Type::FLOAT)]
    protected float $price;

    public function __construct(string $name, float $price)
    {
        $this->name = $name;
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
