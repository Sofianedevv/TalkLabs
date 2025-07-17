<?php

namespace App\DTO;

use App\Enum\CommentStatusEnum;
use Symfony\Component\Validator\Constraints as Assert;


class CommentEditDTO {

    private int $id;

    private string $content;

    private CommentStatusEnum $status;

    public function getId() : int {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }
 
    public function getContent() : string {
        return $this->content;
    }

    public function setContent(string $content) : self {
        $this->content = $content;
        return $this;
    }

        public function getStatus(): CommentStatusEnum
    {
        return $this->status;
    }

    public function setStatus(CommentStatusEnum $status): self
    {
        $this->status = $status;
        return $this;
    }


    






}