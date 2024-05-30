<?php

namespace App\Controller\HistoryApplication;


use App\Entity\JobOffer;
use App\Entity\Application;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/api')]
#[IsGranted('ROLE_ADMIN')]
class FollowApplicationController extends AbstractController
{
    private $entityManager;
    private $applicationRepository;
    private $jobRepository;
    private $mailer;

    public function __construct(EntityManagerInterface $entityManager, MailerInterface $email)
    {
        $this->entityManager = $entityManager;
        $this->applicationRepository = $entityManager->getRepository(Application::class);
        $this->jobRepository = $entityManager->getRepository(JobOffer::class);
        $this->mailer = $email;
    }


    #[Route('/recruiter/confirm/{id}', name: 'app_confirm_recruiter', methods: ['PUT'])]
    public function manageApplicationConfirm(Request $request, int $id): JsonResponse
    {

        $user = $this->getUser();
        if (!$user) {
            return $this->json('Aucun utilisateur connecté');
        }

        $app_status = $this->applicationRepository->find($id);
        if (!$app_status) {
            return $this->json('Candidature introuvable');
        }

        $content = json_decode($request->getContent(), true);
        $userMessage = $content['message'] ?? null;


        $job = $this->jobRepository->findOneBy(['id' => $app_status->getJob()]);
        if (!$job) {
            return $this->json('Aucune correspondance de l\'offre');
        }
        $title = strtoupper($job->getTitle());
        $company = strtoupper($job->getCompany()->getCompanyName());

        if ($app_status->getApplicationStatus() === 'en attente') {

            if (empty($userMessage)) {
                $app_status->setApplicationStatus('reçu');
                $app_status->setMessage("Bonjour,
            \nNous vous remercions d\'avoir postulé pour le poste de $title chez $company.
            \nNous avons bien reçu votre candidature et sommes en train de l\'examiner attentivement. 
            \nNous vous informerons des prochaines étapes du processus de recrutement dès que possible.
            \nCordialement,
            \n$company ");
            } else {
                $app_status->setApplicationStatus('reçu');
                $app_status->setMessage($userMessage);
            }
        } elseif ($app_status->getApplicationStatus() === 'reçu') {

            if (empty($userMessage)) {
                $app_status->setApplicationStatus('confirmée');
                $app_status->setMessage("
            Bonjour,
            \nAprès examen de votre candidature pour le poste de $title, 
            \nnous sommes heureux de vous inviter à un entretien pour discuter davantage de vos compétences et de votre expérience. 
            \nVeuillez nous informer de votre disponibilité pour planifier cet entretien à votre convenance.
            
            \nCordialement,
            \n$company
            
            ");
            } else {
                $app_status->setApplicationStatus('confirmée');
                $app_status->setMessage($userMessage);
            }
        } elseif ($app_status->getApplicationStatus() === 'confirmée') {

            if (empty($userMessage)) {
                $app_status->setApplicationStatus('refusée');
                $app_status->setMessage("
            Bonjour,
            \nJe tiens à vous remercier personnellement pour le temps que vous avez consacré à l\'entretien pour le poste de 
            \n$title et pour l\'intérêt que vous avez montré envers $company.
            \nAprès un examen attentif et des délibérations, nous avons décidé de ne pas poursuivre votre candidature pour ce poste. 
            \nCette décision n\'a pas été facile compte tenu de vos qualités évidentes.
            \nNous espérons que vous ne découragerez pas et que vous continuerez à envisager $company pour des opportunités futures.

            \nNous vous souhaitons tout le succès dans votre recherche d\'emploi et dans votre parcours professionnel.

            \nCordialement,
            \n$company
            
            ");
            } else {
                $app_status->setApplicationStatus('refusée');
                $app_status->setMessage($userMessage);
            }
        }

        $this->entityManager->flush();

        if ($app_status->getApplicationStatus() === 'reçu') {
            $email = (new Email())
                ->from($user->getUserIdentifier())
                ->to($app_status->getCandidate()->getEmail())
                ->subject("Candidature enregistrée pour $title - $company")
                ->text($app_status->getMessage());
           $this->mailer->send($email);
        }
        if ($app_status->getApplicationStatus() === 'confirmée') {
            $email = (new Email())
                ->from($user->getUserIdentifier())
                ->to($app_status->getCandidate()->getEmail())
                ->subject("Invitation à l'entretien pour le poste de  $title chez $company")
                ->text($app_status->getMessage());
           $this->mailer->send($email);
        }
        if ($app_status->getApplicationStatus() === 'refusée') {
            $email = (new Email())
                ->from($user->getUserIdentifier())
                ->to($app_status->getCandidate()->getEmail())
                ->subject("Retour suite à votre entretien pour $title chez $company")
                ->text($app_status->getMessage());
           $this->mailer->send($email);
        }


        return $this->json(['message' => 'l\'état du candidature a changé avec succès']);
    }


    #[Route('/recruiter/refused/{id}', name: 'app_refused_recruiter', methods: ['PUT'])]
    public function manageApplicationRefused(Request $request, int $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json('Aucun utilisateur connecté', 404);
        }

        $app_status = $this->applicationRepository->find($id);
        if (!$app_status) {
            return $this->json(['message' => 'Candidature introuvable']);
        }

        $job = $this->jobRepository->findOneBy(['id' => $app_status->getJob()]);
        if (!$job) {
            return $this->json('Aucune correspondance de l\'offre');
        }
        $content['message'] = json_decode($request->getContent(), true);
        $userMessage = $content['message'] ?? null;

        $title = strtoupper($job->getTitle());
        $company = strtoupper($job->getCompany()->getCompanyName());

       if ($app_status->getApplicationStatus() === 'reçu') {

            if (empty($userMessage)) {
                $app_status->setApplicationStatus('refusée');
                $app_status->setMessage("
                Bonjour,
                \nNous vous remercions d'avoir postulé pour le poste de $title chez $company. 
                \nAprès un examen attentif de votre candidature, nous avons décidé de poursuivre avec d'autres candidats 
                \ndont les compétences et l'expérience correspondent davantage aux besoins de ce poste.
                
                \nNous vous encourageons à continuer de suivre nos offres d'emploi futures et à postuler pour celles qui correspondent à votre profil.
                
                \nNous vous souhaitons le meilleur dans votre recherche d'emploi.
                
                \nCordialement,
                \n$company            
            ");
            } else {
                $app_status->setApplicationStatus('refusée');
                $app_status->setMessage($userMessage);
            }
        }

        $this->entityManager->flush();

        if ($app_status->getApplicationStatus() === 'refusée') {
            $email = (new Email())
                ->from($user->getUserIdentifier())
                ->to($app_status->getCandidate()->getEmail())
                ->subject("Retour suite à votre entretien pour $title chez $company")
                ->text($app_status->getMessage());
           $this->mailer->send($email);
        }

        return $this->json(['message' => 'l\'état du candidature a changé avec succès']);
    }


    #[Route('/recruiter/count-application-status', name: 'app_count_application_status', methods: ['GET'])]
    public function countStatusApplication(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json('Aucun utilisateur connecté', 404);
        }

        $count_pending = count(($this->applicationRepository)->findBy(['application_status' => 'en attente']));

        $count_received = count(($this->applicationRepository)->findBy(['application_status' => 'reçu']));

        $count_confirmed = count(($this->applicationRepository)->findBy(['application_status' => 'confirmée']));

        $count_refused = count(($this->applicationRepository)->findBy(['application_status' => 'refusée']));

        $data = [
            'countPending' => $count_pending,
            'countReceived' => $count_received,
            'countConfirmed' => $count_confirmed,
            'countRefused' => $count_refused
        ];

        return $this->json($data);
    }
}
