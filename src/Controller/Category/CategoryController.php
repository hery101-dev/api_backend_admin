<?php

namespace App\Controller\Category;

use DateTimeImmutable;
use App\Entity\Categories;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/api')]
class CategoryController extends AbstractController
{

    private $categoriesRepository;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->categoriesRepository = $this->entityManager->getRepository(Categories::class);
    }

    #[Route('/category-list', name: 'category_list', methods: ['GET'])]
    public function categories(): JsonResponse
    {
        $categories = $this->categoriesRepository->findAll();

        $categoryData = [];

        foreach ($categories as $category) {
            $categoryData[] = [
                'id' => $category->getId(),
                'categoryName' => $category->getCategoryName(),
                'createdAt' => $category->getCreatedAt() ? $category->getCreatedAt() : null,
                'updatedAt' => $category->getUpdatedAt() ? $category->getUpdatedAt() : null
            ];
        }
        return $this->json($categoryData);
    }

    #[Route('/category-add', name: 'category_add', methods: ['POST'])]
    public function addCategory(Request $request): JsonResponse
    {
        try {
            $content = json_decode($request->getContent(), true);
            if (!$content) {
                return $this->json('Données manquantes requises');
            }
           
            $categories = new Categories();
            $categories->setCategoryName($content['categoryName']);
        
            $this->entityManager->persist($categories);
            $this->entityManager->flush();

            return $this->json('Ajouter avec succès ');
        } catch (\Exception $e) {
            return $this->json($e->getMessage());
        }
    }

    #[Route('/category-edit/{id}', name: 'category_edit', methods: ['PUT'])]
    public function editCategory(Request $request, int $id): JsonResponse
    {
        try {
            $categories = $this->categoriesRepository->find($id);

            if (!$categories) {
                return $this->json('Catégorie pour l\'id = ' . $id);
            }
            $content = json_decode($request->getContent(), true);
            if (!$content) {
                return $this->json('Données manquantes requises');
            }
            $categories->setCategoryName($content['categoryName']);
            $categories->setUpdatedAt(new DateTimeImmutable());

            $this->entityManager->flush();

            return $this->json('Modifier avec succès ' . $id);
        } catch (\Exception $e) {
            return $this->json($e->getMessage());
        }
    }

    #[Route('/category-remove/{id}', name: 'category_remove', methods: ['DELETE'])]
    public function removeCategory(int $id): JsonResponse
    {
        try {
            $categories = $this->categoriesRepository->find($id);

            if (!$categories) {
                return $this->json('Catégorie pour l\'id = ' . $id);
            }

            $this->entityManager->remove($categories);
            $this->entityManager->flush();

            return $this->json('Suppression avec succès ' . $id);
        } catch (\Exception $e) {
            return $this->json($e->getMessage());
        }
    }
}
