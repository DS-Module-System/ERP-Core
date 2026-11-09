<?php

namespace App\Trait\Core;

trait EnumLabelTrait
{
    private function getClassName(): ?string
    {
        return basename(str_replace('\\', '/', get_class($this)));
    }

    abstract private function getDomain(): ?string;

    public function getLabelInForm(): string
    {
        return $this->getClassName() . '.' . $this->getLabel();
    }

    public function getLabelInTable(): string
    {
        return $this->getDomain() . '.' . $this->getLabel();
    }
} 