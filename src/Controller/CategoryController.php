<?php

namespace App\Controller;

use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name:"app_")]
final class CategoryController extends AbstractController
{
    #[Route('/categories', name: 'categories')]
    public function getCategories(CategoryService $categoryService): JsonResponse {

        return $this->json($categoryService->getAllCategories(), Response::HTTP_OK);

    }
}
