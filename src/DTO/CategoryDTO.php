<?php

namespace App\DTO;

class CategoryDTO {

    private int $id;
    private string $name;
    private string $shortName;


    public function getId() : int {
        return $this->id;
    }

    public function setId(int $id) : self {
        $this->id = $id;
        return $this;
    }

    public function getName() : string {
        return $this->name;
    }

    public function setName(string $name) : self {
        $this->name = $name;
        return $this;
    }


    public function getShortName() :string {
        return $this->shortName;
    }

    public function setShortName(string $shortName) : self {
        $this->shortName = $shortName;
        return $this;
    }
}