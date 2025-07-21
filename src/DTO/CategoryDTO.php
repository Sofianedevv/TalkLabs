<?php

namespace App\DTO;

class CategoryDTO {

    private string $name;
    private string $shortName;

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