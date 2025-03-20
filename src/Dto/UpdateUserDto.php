<?php

namespace App\Dto;

use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

final class UpdateUserDto
{
  #[Assert\NotBlank]
  #[Groups(['user:write'])]
  public $firstname;
  
  #[Assert\NotBlank]
  #[Groups(['user:write'])]
  public $lastname;

}