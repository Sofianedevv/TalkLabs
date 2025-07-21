<?php

namespace App\Service;

use App\DTO\CategoryDTO;
use App\DTO\CategoryEditDTO;
use App\DTO\CategoryReadDTO;
use App\Mapper\CategoryMapper;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class CategoryService {

    private CategoryRepository $categoryRepository;
    private CategoryMapper $categoryMapper;
    private EntityManagerInterface $em;

    public function __construct(CategoryRepository $categoryRepository, CategoryMapper $categoryMapper, EntityManagerInterface $em) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryMapper = $categoryMapper;
        $this->em = $em;
    }

    public function addCategory (CategoryDTO $dto): CategoryReadDTO {

        $category = $this->categoryMapper->dtoToCategory($dto);
        $this->em->persist($category);
        $this->em->flush();

        return $this->categoryMapper->categoryToReadDTO($category);

    }

    public function editCategory($id, CategoryEditDTO $dto) : CategoryReadDTO {

        $category  = $this->categoryRepository->find($id);

        if (!$category) {
            throw new \Exception("Categorie introuvable");
        }

        $updatedCategory = $this->categoryMapper->editDtoToCategory($dto, $category);
        $this->em->persist($updatedCategory);
        $this->em->flush();
        return $this->categoryMapper->categoryToReadDTO($updatedCategory);
    }


    public function getAllCategories() : array {
        $categories = $this->categoryRepository->findAll();
        $allCategories = [];

        foreach($categories as $category) {
            $allCategories[] = $this->categoryMapper->categoryToReadDTO($category);
        }
        return $allCategories;
    }
    
    public function getCategoryById(int $id): CategoryReadDTO
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            throw new \Exception("Catégorie introuvable avec l'ID $id.");
        }

        return $this->categoryMapper->categoryToReadDTO($category);
    }


    public function deleteCategory($id) : void {

         $category  = $this->categoryRepository->find($id);

        if (!$category) {
            throw new \Exception("Categorie introuvable");
        }

        $this->em->remove($category);
        $this->em->flush();

    }
}