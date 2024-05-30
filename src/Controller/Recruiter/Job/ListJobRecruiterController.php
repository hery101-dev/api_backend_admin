<?php

namespace App\Controller\Recruiter\Job;


use App\Entity\JobOffer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[IsGranted('ROLE_ADMIN')]
class ListJobRecruiterController extends AbstractController
{

    private $jobListRepository;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->jobListRepository = $entityManager->getRepository(JobOffer::class);
    }

    #[Route('/recherche', name: 'recherche_id_offre', methods: ['GET'])]
    public function recherhJob(): JsonResponse
    {
        $job = $this->jobListRepository->findBy(['id' => [2668, 1996, 2040, 1853, 3233]]);
        dd($job);

        return $this->json('');
    }


    #[Route("/api/admin/job-list", name: "job_list_admin_dashboard", methods: ["GET"])]
    public function listJob(RequestStack $requestStack): JsonResponse
    {
        $userActuel = $this->getUser();
        if (!$userActuel) {
            return $this->json('Aucun utilisateur connecté', 404);
        }
        $jobOffers = $this->jobListRepository->findAll();

        $data = [];

        foreach ($jobOffers as $jobOffer) {

            $jobData = [
                'id' => $jobOffer->getId(),
                'title' => $jobOffer->getTitle(),
                'description' => $jobOffer->getDescription(),
                'salary' => $jobOffer->getSalary(),
                'deadline' => $jobOffer->getDeadlineAt(),
                'createdAt' => $jobOffer->getCreatedAt(),
                'status' => $jobOffer->isJobStatus(),
            ];
            $contratsCollection = $jobOffer->getContrats();
            foreach ($contratsCollection as $contrat) {
                $jobData['contrat'][] = $contrat->getType();
            }

            $category = $jobOffer->getCategory();
            if ($category) {
                $jobData['category'] = $category->getCategoryName();
            }
            $location = $jobOffer->getLocation();
            if ($location) {
                $jobData['address'] = $location->getAddress();
                $jobData['city'] = $location->getCity();
                $jobData['country'] = $location->getCountry();
            }
            $company = $jobOffer->getCompany();
            if ($company) {
                $logoPath = 'assets/upload/image/' . $company->getLogo();
                $baseUrl = $requestStack->getCurrentRequest()->getSchemeAndHttpHost();
                $jobData['company'] = $company->getCompanyName();
                $jobData['website'] = $company->getWebsite();
                $jobData['logo'] = $baseUrl.'/'. $logoPath;
                $jobData['company_detail'] = $company->getCompanyDetail();
            }
            $data[] = $jobData;
        }
        return $this->json($data);
    }
}
