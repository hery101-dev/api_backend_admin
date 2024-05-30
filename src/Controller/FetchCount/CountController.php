<?php

namespace App\Controller\FetchCount;

use App\Entity\Application;
use App\Entity\File;
use App\Entity\JobOffer;
use App\Entity\OfferView;
use App\Entity\Recommandation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[IsGranted('ROLE_ADMIN')]
class CountController extends AbstractController
{

    #[Route('/api/admin/count-user', name: 'app_admin_count_user', methods: ['GET'])]
    public function countUser(EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json('Aucun utilisateur connecté', 404);
        }

        $countUser = count($entityManager->getRepository(User::class)->findAll());

        $countRecruiter = count($entityManager->getRepository(User::class)->findBy(['userType' => 'recruiter']));

        $countCandidate = count($entityManager->getRepository(User::class)->findBy(['userType' => 'candidate']));

        $countApplication = count($entityManager->getRepository(Application::class)->findAll());

        $countJob = count($entityManager->getRepository(JobOffer::class)->findAll());

        $countVue = count($entityManager->getRepository(OfferView::class)->findAll());

        $countRecommend = count($entityManager->getRepository(Recommandation::class)->findAll());

        $countCV = count($entityManager->getRepository(File::class)->findAll());


        $data = [
            'countUser' => $countUser,
            'countRecruiter' => $countRecruiter,
            'countCandidate' => $countCandidate,
            'countApplication' => $countApplication,
            'countJob' => $countJob,
            'countView' => $countVue,
            'countRecommend' => $countRecommend,
            'countCv' => $countCV
        ];

        return $this->json($data);
    }
}