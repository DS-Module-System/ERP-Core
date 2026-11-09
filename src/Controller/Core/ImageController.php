<?php

namespace App\Controller\Core;

use App\Entity\Core\CoreEntity;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

class ImageController extends AbstractController
{
    #[Route(path: '/uploads/optimized/{width<\d+>}x{height<\d+>}_{imageName}.{extension}', name: 'image_scale_default')]
    public function defaultIndex(EntityManagerInterface $em, int $width, int $height, string $imageName, string $extension): mixed
    {
        return $this->imageProcess($em, '', $width, $height, $imageName, $extension);
    }

    #[Route(path: '/uploads/{subPath}/optimized/{width<\d+>}x{height<\d+>}_{imageName}.{extension}', name: 'image_scale', requirements: ['subPath' => '.+'])]
    public function index(EntityManagerInterface $em, string $subPath, int $width, int $height, string $imageName, string $extension): mixed
    {
        return $this->imageProcess($em, $subPath, $width, $height, $imageName, $extension);
    }

    private function imageProcess(
        EntityManagerInterface $em,
        string $subPath,
        int $width,
        int $height,
        string $imageName,
        string $extension
    ): mixed {

        ini_set('memory_limit', -1);

        if (!empty($subPath)) {
            $uploadDir = $this->getParameter('upload_directory') . "/{$subPath}";
        } else {
            $uploadDir = $this->getParameter('upload_directory');
        }

        $imageSizes = $this->getParameter('image_sizes');
        if ($width !== 0 || $height !== 0) {
            if (array_search("{$width}x{$height}", $imageSizes) === false) {
                throw $this->createNotFoundException('Image width and height not allow');
            }
        }
        $manager = new ImageManager(new Driver());
        $originalExtension = $extension;

        if ($extension == 'webp') {
            $imageFileExist = false;
            foreach (['jpg', 'jpeg', 'png'] as $ext) {
                $imagePath = "{$uploadDir}/{$imageName}.{$ext}";
                if (file_exists($imagePath) === true) {
                    $imageFileExist = true;
                    $originalExtension = $ext;
                    break;
                }
            }

            if ($imageFileExist === false) {
                $imagePath = "{$uploadDir}/{$imageName}.{$originalExtension}";
                if (file_exists($imagePath) === false) {
                    throw $this->createNotFoundException('Image Not Found');
                }
            }
        }

        $imagePath = "{$uploadDir}/{$imageName}.{$originalExtension}";
        if (file_exists($imagePath) === false) {
            throw $this->createNotFoundException('Image Not Found');
        }
        $imageObj = $manager->read($imagePath);
        if ($width === 0 && $height === 0) {
            //TODO trqbva da se opravi!
//            $coreSize = $imageObj->driver();
//            $imageObj->resize(imagesx($coreSize), imagesy($coreSize), function ($constraint) {
//                $constraint->aspectRatio();
//                $constraint->upsize();
//            });
        } else {
            if ($width === 0 || $height === 0) {
                // resize the image to a width of $width or $height  and constrain aspect ratio (auto height)
                $tmpWidth = $width === 0 ? null : $width;
                $tmpHeight = $height === 0 ? null : $height;
                $imageObj->resize($tmpWidth, $tmpHeight);
            } else {
                // resize the image so that the largest side fits within the limit; the smaller
                // side will be scaled to maintain the original aspect ratio
                $imageObj->resize($width, $height);
            }
        }

        if (file_exists($uploadDir . '/optimized') === false) {
            mkdir($uploadDir . '/optimized', 0755, true);
        }

        $imageObj->save("{$uploadDir}/optimized/{$width}x{$height}_{$imageName}.{$extension}", 70, $extension);
//        return readfile("");


        $response = new BinaryFileResponse("{$uploadDir}/optimized/{$width}x{$height}_{$imageName}.{$extension}");
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            basename("{$uploadDir}/optimized/{$width}x{$height}_{$imageName}.{$extension}")
        );

        return $response;

//        return $imageObj->response();
    }
}
