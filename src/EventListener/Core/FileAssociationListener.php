<?php

namespace App\EventListener\Core;


use App\Entity\Core\FileAssociation;
use App\Service\Core\FileService;
use Doctrine\ORM\EntityManagerInterface;

class FileAssociationListener
{

    public function __construct(private readonly EntityManagerInterface $em, private readonly FileService $fileService)
    {
    }

    public function postRemove(FileAssociation $fileAssociation): void
    {

        if($fileAssociation->getFile() && $fileAssociation->getFile()->getId()) {
            $file = $this->em->createQuery('SELECT f FROM App\Entity\Core\File f WHERE f.id = '.$fileAssociation->getFile()->getId())->getOneOrNullResult();
//            $file = $this->em->getRepository(File::class)->find($fileAssociation->getFile());
            if($file) {
                $this->em->createQuery('DELETE FROM App\Entity\Core\File f WHERE f.id = '.$file->getId())->getOneOrNullResult();
                $this->fileService->removeFileFromFileSystem($file);
//                Kogato e s EntityManager-a dava grehska!!!
//                $this->em->remove($file);
//                $this->em->flush();
            }
        }
    }
}
