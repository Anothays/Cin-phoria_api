<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document]
class Product
{
  #[MongoDB\Id]
  private string $id;

  #[MongoDB\Field(type: 'string')]
  private string $name;

  #[MongoDB\Field(type: 'float')]
  private float $price;

  public function getId()
  {
    return $this->id;
  }

  public function setId(int $id): static
  {
    $this->id = $id;
    return $this;
  }

  public function getName()
  {
    return $this->name;
  }

  public function setName(string $name): static
  {
    $this->name = $name;
    return $this;
  }

  public function getPrice()
  {
    return $this->price;
  }

  public function setPrice(int $price)
  {
    $this->price = $price;
    return $this;
  }
}