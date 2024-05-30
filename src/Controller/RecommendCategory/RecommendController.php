<?php

namespace App\Controller\RecommendCategory;

use App\Entity\JobOffer;
use App\Entity\Categories;
use App\Entity\Application;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[IsGranted('ROLE_CANDIDATE')]
class RecommendController extends AbstractController
{
    private $jobRepository;
    private $applicationRepository;
    private $entityManager;
    private $categoriesRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->applicationRepository = $this->entityManager->getRepository(Application::class);
        $this->jobRepository = $this->entityManager->getRepository(JobOffer::class);
        $this->categoriesRepository = $this->entityManager->getRepository(Categories::class);
    }


    #[Route('/api/candidate/recommend', name: 'app_recommend_system_jobOffer', methods: ['GET'])]
    public function listRecommended(RequestStack $requestStack, SerializerInterface $serializer)
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json('Aucun utilisateur trouvé', 404);
        }

        $candidate = $this->applicationRepository->findBy(['candidate' => $user]);
        if (!$candidate) {
            return $this->json('Aucun candidature trouvé');
        }
        foreach ($candidate as $value) {
            $getCategoryApp =  $value->getCategoryJob();
        }

        $countCandidate = $this->applicationRepository->count(['categoryJob' =>  $getCategoryApp, 'candidate' => $user]);
        $computerScienceOffers = [];
        if ($countCandidate >= 2) {
            foreach ($candidate as $application) {
                $category = $application->getCategoryJob();

                $allJobs = $this->jobRepository->findAll();

                foreach ($allJobs as $value) {
                    $matchingJobs = array_filter($allJobs, function ($value) use ($category) {
                        return $category === $value->getCategory()->getCategoryName();
                    });

                    foreach ($matchingJobs as $value) {
                        $jobId = $value->getId();
                        $contratsCollection =  $value->getContrats();
                        $contratsData = [];
                        foreach ($contratsCollection as $contrat) {
                            $contratsData[] = $contrat->getType();
                        }
                        $location = $value->getLocation();
                        $company = $value->getCompany();
                        $logoPath = 'assets/upload/image/' . $company->getLogo();
                        $baseUrl = $requestStack->getCurrentRequest()->getSchemeAndHttpHost();

                        if (!isset($uniqueJobIds[$jobId])) {

                            $jobData = [
                                'id' => $jobId,
                                'title' => $value->getTitle(),
                                'description' => $value->getDescription(),
                                'salary' => $value->getSalary(),
                                'createdAt' => $value->getCreatedAt()->format('c'),
                                'deadlineAt' => $value->getDeadlineAt(),
                                'status' => $value->isJobStatus(),
                                'contrat' =>  $contratsData,
                                'address' => $location ? $location->getAddress() : null,
                                'city' => $location ? $location->getCity() : null,
                                'country' => $location ? $location->getCountry() : null,
                                'logo' => $company ?  $baseUrl . '/' . $logoPath : null,
                                'company_name' => $company ? $company->getCompanyName() : null,
                                'website' => $company ? $company->getWebsite() : null,
                                'company_detail' => $company ? $company->getCompanyDetail() : null,
                            ];
                          
                            if ($value->isJobStatus() === true) {
                                $computerScienceOffers[] = $jobData;
                            }

                            $uniqueJobIds[$jobId] = true;
                            $jsonContent = $serializer->serialize($computerScienceOffers, 'json');
                        }
                    }
                }
            }
        }

        return new JsonResponse($jsonContent, 200, [], true);
    }
}
