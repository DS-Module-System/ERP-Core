<?php

namespace App\DataTransformer\Core;

use App\Entity\Core\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class FileToIntegerTransformer implements DataTransformerInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Transforms an object (file) to an integer (number).
     */
    public function transform(mixed $file): string
    {
        if (null === $file) {
            return '';
        }

        return $file->getId();
    }

    /**
     * Transforms an integer (number) to an object (file).
     */
    public function reverseTransform(mixed $integer): ?File
    {
        // no integer number? It's optional, so that's ok
        if (!$integer) {
            return null;
        }

        $file = $this->entityManager
            ->getRepository(File::class)
            // query for the issue with this id
            ->find($integer)
        ;

        if (null === $file) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'An issue with number "%s" does not exist!',
                $integer
            ));
        }

        return $file;
    }
}
