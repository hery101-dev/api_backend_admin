<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

#[Route('/api')]
class UserController extends AbstractController
{
  #[Route('/user', name: 'api_me', methods: ['GET'])]
  public function apiGetUser(
    EntityManagerInterface $entityManager
  ): Response {
    $user = $this->getUser();
    if (!$user) {
      return $this->json('Aucun utilisateur trouvé', 404);
    }
    $userBD = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getUserIdentifier()]);
    if (!$userBD) {
      return $this->json('Aucune correspondance pour l\'email');
    }
    $data = [
      'id' => $userBD->getId(),
      'email' => $user->getUserIdentifier(),
      'username' => $userBD->getUsername(),
      'enabledRecommandation' => $userBD->isRecommendationsEnabled(),
    ];
    return $this->json($data);
  }

  #[Route('/user/get-all-user', name: 'get_user_all', methods: ['GET'])]
  public function getAllUser(
    EntityManagerInterface $entityManager
  ): Response {

    $users = $entityManager->getRepository(User::class)->findAll();
    if (!$users) {
      return $this->json('Aucune données');
    }

    $data = [];

    foreach ($users as $user) {
      $data[] = [
        'id' => $user->getId(),
        'email' => $user->getEmail(),
        'username' => $user->getUsername() ? $user->getUsername() : null,
        'userType' => $user->getUserType(),
        'verified' => $user->isIsVerified(),
        'createdAt' => $user->getCreatedAt()
      ];
    }
    return $this->json($data);
  }

  #[Route('/user/active/{id}', name: 'active_user_id', methods: ['PUT'])]
  public function activeUser(
    EntityManagerInterface $entityManager,
    int $id
  ): Response {

    $userBD = $entityManager->getRepository(User::class)->find($id);
    if (!$userBD) {
      return $this->json('Aucune correspondance de l\'utilisateur');
    }

    $active = $userBD->isIsVerified();
    $userBD->setIsVerified(!$active);

    $entityManager->flush();

    return $this->json(['active' => $userBD->isIsVerified()]);
  }


  #[Route('/user/edit', name: 'app_user_edit', methods: ['PUT', 'PATCH'])]
  public function edit(
    Request $request,
    User $user,
    EntityManagerInterface $entityManager,
    JWTTokenManagerInterface $jwtManager
  ): JsonResponse {
    $user = $this->getUser();
    if (!$user) {
      return $this->json('Aucun utilisateur trouvé', 404);
    }
    $findUser = $entityManager->getRepository(User::class)->find($user);
    if (!$findUser) {
      return $this->json('Aucun utilisateur correspondant');
    }

    $content = json_decode($request->getContent(), true);
    if (!$content) {
      return $this->json('Données manquantes');
    }

    $findUser->setEmail($content['email']);
    try {
      $entityManager->flush();
    } catch (\Exception $e) {
      return new JsonResponse(['error' => $e->getMessage()], 500);
    }
    $token = $jwtManager->create($user);

    $data = [
      'email' => $findUser->getEmail(),
      'password' => $findUser->getPassword(),
      'token' => $token,
    ];

    return $this->json($data);
  }



  #[Route('/user/registrations', name: 'api_user_registrations', methods: ['GET'])]
  public function getUserRegistrations(UserRepository $userRepository): JsonResponse
  {
    $registrations = $userRepository->countRegistrationsByDate();

    $dates = array_map(function ($registration) {
      return $registration['registrationDate'];
    }, $registrations);

    $counts = array_map(function ($registration) {
      return $registration['count'];
    }, $registrations);

    return $this->json([
      'dates' => $dates,
      'counts' => $counts
    ]);
  }
}
