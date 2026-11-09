<?php
/**
 * Created by PhpStorm.
 * User: Ervin
 * Date: 2019-07-09
 * Time: 14:10
 */

namespace App\EventListener\Core;

use App\Entity\Core\File;
use App\Service\Core\FileService;

class FileListener
{
    public function __construct(private readonly FileService $fileService)
    {
    }

    public function postRemove(File $file): void
    {
        $this->fileService->removeFileFromFileSystem($file);
    }

}
