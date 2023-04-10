<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserService
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private JWTTokenManagerInterface $jwtManager,
        private UserRepository $userRepository
    ) {
    }

    /**
     * @see https://symfony.com/doc/current/LexikJWTAuthenticationBundle/9-access-authenticated-jwt-token.html
     * @return User
     * @throws JWTDecodeFailureException
     */
    public function getCurrentUser(): User
    {
        $result = $this->jwtManager->decode($this->tokenStorage->getToken());

        return $this->userRepository->findOneBy(['email' => $result['username']]);
    }
}
