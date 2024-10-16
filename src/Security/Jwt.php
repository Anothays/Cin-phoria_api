<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class Jwt {

  public function __construct( private RequestStack $requestStack, private JWTEncoderInterface $jwtManager) {}

  public function decodeJwt(string $jwt) 
  {
    try {
      return $this->jwtManager->decode($jwt);
    } catch (\Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException $th) {
      return false;
    }
    return false;
  }

  public function getJwtFromHttpHeaders()
  {
    $request = $this->requestStack->getCurrentRequest();
    $authHeader = $request->headers->get('Authorization');
    if (!$authHeader) return null;
    if (str_starts_with($authHeader, 'Bearer ')) {
      $token = substr($authHeader, 7); // Extraction du token après 'Bearer '
      return $token;
    }
    return null;
  }

}