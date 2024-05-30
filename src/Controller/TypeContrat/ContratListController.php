<?php

namespace App\Controller\TypeContrat;

use App\Entity\Contrat;
use App\Entity\JobOffer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


#[Route('/api')]
class ContratListController extends AbstractController
{
    #[Route('/contrat-list', name: 'contrat_list', methods: ['GET'])]
    public function indexContrat(EntityManagerInterface $entityManager): JsonResponse
    {
        $contrats = $entityManager->getRepository(Contrat::class)->findAll();

        $dataContrat = [];

        foreach ($contrats as $contrat) {
            $dataContrat[] = [
                'id' => $contrat->getId(),
                'type' => $contrat->getType()
            ];
        }
        return $this->json($dataContrat);
    }

    #[Route('/contrat-add', name: 'contrat_add', methods: ['POST'])]
    public function addContrat(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $content = json_decode($request->getContent(), true);
        if (!$content) {
            return $this->json('Données manquantes requises');
        }

        $contrat = new Contrat;
        $contrat->setType($content['contrat']);

        $entityManager->persist($contrat);
        $entityManager->flush();

        return $this->json('Ajouter avec succès');
    }

    #[Route('/contrat-edit/{id}', name: 'contrat_edit', methods: ['PUT'])]
    public function editContrat(Request $request, int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $contrat = $entityManager->getRepository(Contrat::class)->find($id);
        if (!$contrat) {
            return $this->json('Aucun contrat correspondant');
        }

        $content = json_decode($request->getContent(), true);
        if (!$content) {
            return $this->json('Données manquantes requises');
        }

        $contrat->setType($content['contrat']);

        $entityManager->flush();

        return $this->json('Modifier avec succès');
    }



    #[Route('/contrat-remove/{id}', name: 'contrat_remove', methods: ['DELETE'])]
    public function removeContrat(int $id, EntityManagerInterface $entityManager): JsonResponse
    {

        $contrat = $entityManager->getRepository(Contrat::class)->find($id);
        if (!$contrat) {
            return $this->json('Aucun contrat correspondant');
        }

        $entityManager->remove($contrat);
        $entityManager->flush();

        return $this->json('Suppression avec succès');
    }
}
