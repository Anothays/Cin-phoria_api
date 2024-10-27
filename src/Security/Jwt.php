<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class Jwt {

  public function __construct(
    private RequestStack $requestStack, 
    private TokenStorageInterface $tokenStorageInterface, 
    private JWTTokenManagerInterface $jwtManager
  ) {}

  public function decodeJwt() 
  {
    try {
      return $this->jwtManager->decode($this->tokenStorageInterface->getToken());
    } catch (\Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException $th) {
      return false;
    }
    return false;
  }

  public function getJwtFromHttpHeaders()
  {
    return $this->tokenStorageInterface->getToken();
  }

}