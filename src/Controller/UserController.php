<?php

namespace App\Controller;

use App\Service\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/user')]
#[OA\Tag(name: 'User')]
class UserController extends AbstractController
{
    /**
     * @throws JWTDecodeFailureException
     */
    #[Route('', name: 'api_current_user', methods: ['GET'])]
    #[OA\Get(summary: 'Return current user.')]
    public function currentUser(UserService $userService): JsonResponse
    {
        return $this->json($userService->getCurrentUser(), context: ['groups' => 'getCurrentUser']);
    }
}
