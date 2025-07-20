<?php

namespace App\Controller;

use App\Entity\Category;
use App\DTO\CategoryDTO;
use App\DTO\CategoryEditDTO;
use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name:"app_")]
final class CategoryController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/add-category', name: 'add_category')]
    public function addCategory(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, CategoryService $categoryService): JsonResponse
    {
        $dto = $serializer->deserialize($request->getContent(), CategoryDTO::class, 'json');
        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        try {
            $category =  $categoryService->addCategory($dto);

            return $this->json([
                'category' => $category,
                'message' => 'Categorie ajouté avec succès'
            ]);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/edit-category/{categoryId}', name: 'edit_category')]
    public function editCategory(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, CategoryService $categoryService, $categoryId): JsonResponse
    {
        $dto = $serializer->deserialize($request->getContent(), CategoryEditDTO::class, 'json');
        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors]);
        }

        try {
            $category = $categoryService->editCategory($categoryId, $dto);
            return $this->json([
                'category' => $category,
                'message' => 'Categorie mis à jour avec succès'
            ]);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/delete-category/{categoryId}', name: 'delete_category')]
    public function deleteCategory(
        CategoryService $categoryService,
        $categoryId
    ): JsonResponse {
        try {
            $categoryService->deleteCategory($categoryId);
            return $this->json(['message' => 'Categorie supprimé avec succès']);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/categories', name: 'categories')]
    public function getCategories(CategoryService $categoryService): JsonResponse {

        return $this->json($categoryService->getAllCategories(), Response::HTTP_OK);
    }

    #[Route('/category/{categoryId}', name: 'category')]
    public function getCategory(CategoryService $categoryService, $categoryId): JsonResponse {

        return $this->json($categoryService->getCategoryById($categoryId), Response::HTTP_OK);
    }
}
