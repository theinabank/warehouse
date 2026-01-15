<?php

namespace App\Exceptions;

use Exception;

class ProductUpdateException extends Exception
{
    private array $productErrorMessages;

    public function __construct(array $productErrorMessages = [])
    {
        $this->productErrorMessages = $productErrorMessages;

        return parent::__construct($this->productErrorMessages[0] ?? '', 0, null);
    }

    public function getErrorMessages()
    {
        return $this->productErrorMessages;
    }
}
