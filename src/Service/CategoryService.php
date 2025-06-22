<?php

namespace App\Service;

use App\Dto\CategoryDTO;
use App\Mapper\CategoryMapper;
use App\Repository\CategoryRepository;


class CategoryService {

    private CategoryRepository $categoryRepository;
    private CategoryMapper $categoryMapper;

    public function __construct(CategoryRepository $categoryRepository, CategoryMapper $categoryMapper) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryMapper = $categoryMapper;
    }


    public function getAllCategories() : array {
        $categories = $this->categoryRepository->findAll();
        $allCategories = [];

        foreach($categories as $category) {
            $allCategories[] = $this->categoryMapper->categoryToDTO($category);
        }
        return $allCategories;
    }


}