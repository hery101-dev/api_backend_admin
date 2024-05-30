<?php

namespace App\Controller\Candidature;

use App\Entity\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ListeCandidatureController extends AbstractController
{
    
    #[Route('/api/admin/application-admin-list', name: 'application_history_admin', methods: ['GET'])]
    public function getApplicationRecruiter(EntityManagerInterface $entityManager): Response
    {
        $applications = $entityManager->getRepository(Application::class)->findAll();

        $data = [];
        foreach ($applications as $application) {
            $jobApp = $application->getJob();
            $company = $jobApp->getCompany();
            $candidate = $application->getCandidate();

            $data[] = [
                'id' => $application->getId(),
                'submitedAt' => $application->getSubmitedAt(),                
                'candidate' => $candidate ? $candidate->getEmail() : null,
                'company' => $company ? $company->getCompanyName() : null,
                'job' => $jobApp ? $jobApp->getTitle() : null,
                'status' => $application->getApplicationStatus(),
            ];
        }
        return new JsonResponse($data);
    }

}