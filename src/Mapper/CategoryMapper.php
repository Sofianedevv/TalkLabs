<?php 

namespace App\Mapper;

use App\DTO\CategoryDTO;
use App\Entity\Category;

class CategoryMapper {

    public function categoryToDTO(Category $category) : CategoryDTO {
        $dto = new CategoryDTO();
        $dto->setId($category->getId());
        $dto->setName($category->getName());
        $dto->setShortName($category->getShortName());
        return $dto; 
    }



}