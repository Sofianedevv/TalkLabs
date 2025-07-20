<?php 

namespace App\Mapper;

use App\DTO\CategoryDTO;
use App\DTO\CategoryEditDTO;
use App\DTO\CategoryReadDTO;
use App\Entity\Category;

class CategoryMapper {

    public function dtoToCategory(CategoryDTO $dto) : Category {
        $category= new Category();
        $category->setName($dto->getName());
        $category->setShortName($dto->getShortName());
        $category->setCreatedAt(new \DateTimeImmutable());

        return $category; 
    }
    public function editDtoToCategory($dto, Category $category): Category
    {
        $category->setName($dto->getName());
        $category->setShortName($dto->getShortName());

        return $category;
    }

    public function categoryToDTO(Category $category) : CategoryEditDTO {
        $dto = new CategoryEditDTO();
        $dto->setName($category->getName());
        $dto->setShortName($category->getShortName());
        return $dto;
    }
    public function categoryToReadDTO(Category $category): CategoryReadDTO
    {
        $dto = new CategoryReadDTO();
        $dto->setId($category->getId());
        $dto->setName($category->getName());
        $dto->setShortName($category->getShortName());
        $dto->setCreatedAt($category->getCreatedAt()->format('Y-m-d H:i:s'));

        return $dto;
    }


}