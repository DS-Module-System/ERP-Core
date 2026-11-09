<?php

namespace App\Twig\Core;

use App\Entity\Core\File;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ImageExtension extends AbstractExtension
{


    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function getImageById(?int $imageId): File|null
    {
        if($imageId == null) {
            return null;
        }
        return $this->em->getRepository(File::class)->find($imageId);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_image_by_id', $this->getImageById(...)),
        ];
    }



}
