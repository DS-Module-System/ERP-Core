<?php

namespace App\Entity\Core;

use App\Repository\Core\SettingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingRepository::class)]
class Setting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $property = null;

    /**
     * @var array<mixed>|null
     */
    #[ORM\Column(nullable: true)]
    private ?array $value = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProperty(): ?string
    {
        return $this->property;
    }

    public function setProperty(string $property): self
    {
        $this->property = $property;

        return $this;
    }

    /**
     * @return array<mixed>|null
    */
    public function getValue(): ?array
    {
        return $this->value;
    }

    /**
     * @param array<mixed>|null $value
    */
    public function setValue(?array $value): self
    {
        $this->value = $value;

        return $this;
    }
}
