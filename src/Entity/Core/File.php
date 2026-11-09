<?php

namespace App\Entity\Core;

use App\Repository\Core\FileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FileRepository::class)]
#[ORM\HasLifecycleCallbacks]
class File implements CoreEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 2048)]
    private ?string $originalName = null;

    #[ORM\Column(type: Types::STRING, length: 2048)]
    private ?string $uniqueFilename = null;

    #[ORM\Column(type: Types::STRING, length: 25, options: ['fixed' => true, 'collation' => 'ascii_bin'])]
    private ?string $type = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $width = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $height = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $size = null;

    #[ORM\Column(type: Types::STRING, length: 2048, nullable: true)]
    private ?string $altText = null;

    #[ORM\Column(type: Types::STRING, length: 2048)]
    private ?string $webDir = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'file', targetEntity: FileAssociation::class, cascade: ['persist', 'remove'])]
    private Collection $fileAssociations;

    public function __construct()
    {
        $this->fileAssociations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(string $originalName): self
    {
        $this->originalName = $originalName;

        return $this;
    }

    public function getUniqueFilename(): ?string
    {
        return $this->uniqueFilename;
    }

    public function setUniqueFilename(string $uniqueFilename): self
    {
        $this->uniqueFilename = $uniqueFilename;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getAltText(): ?string
    {
        return $this->altText;
    }

    public function setAltText(?string $altText): self
    {
        $this->altText = $altText;

        return $this;
    }

    public function getWebDir(): ?string
    {
        return $this->webDir;
    }

    public function setWebDir(string $webDir): self
    {
        $this->webDir = $webDir;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->setCreatedAt(new \DateTimeImmutable());
    }

    /**
     * @return Collection<int, FileAssociation>
     */
    public function getFileAssociations(): Collection
    {
        return $this->fileAssociations;
    }

    public function addFileAssociation(FileAssociation $fileAssociation): self
    {
        if (!$this->fileAssociations->contains($fileAssociation)) {
            $this->fileAssociations->add($fileAssociation);
            $fileAssociation->setFile($this);
        }

        return $this;
    }

    public function removeFileAssociation(FileAssociation $fileAssociation): self
    {
        if ($this->fileAssociations->removeElement($fileAssociation)) {
            // set the owning side to null (unless already changed)
            if ($fileAssociation->getFile() === $this) {
                $fileAssociation->setFile(null);
            }
        }

        return $this;
    }

    public function getFullUrl(): string
    {
        return "{$this->getWebDir()}/{$this->getUniqueFilename()}";
    }

    public function getOptimizedFullUrl(int $width, int $height, string $extention = ''): string
    {
        if(empty($extention)) {
            return "{$this->getWebDir()}/optimized/{$width}x{$height}_{$this->getUniqueFilename()}";
        } elseif ($extention == 'webp') {
            $tmpFileName = explode('.', $this->getUniqueFilename());
            return "{$this->getWebDir()}/optimized/{$width}x{$height}_{$tmpFileName[0]}.webp";
        }
        return '';
    }
}
