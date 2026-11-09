<?php
/**
 * Created by PhpStorm.
 * User: Ervin
 * Date: 2019-07-09
 * Time: 09:05
 */

namespace App\Service\Core;


use App\Entity\Core\File;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use PHPUnit\Util\Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileService
{

    public function __construct(
        private readonly string $targetDirectory,
        private readonly string $webDirectory,
        private readonly EntityManagerInterface $em,
        private readonly SluggerInterface $slugger,
        private readonly ParameterBagInterface $params
    )
    {
    }

    /**
     * @return string
     */
    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }

    public function scaleImageFile(string $file, int $width = 800, int $height = 0): string|false
    {

        $source_pic = $file;
        $max_width = $width;
        $max_height = $height;

        [$width, $height, $image_type] = getimagesize($file);

        switch ($image_type) {
            case 1:
                $src = imagecreatefromgif($file);
                break;
            case 2:
                $src = imagecreatefromjpeg($file);
                break;
            case 3:
                $src = imagecreatefrompng($file);
                break;
            default:
                return '';
        }

        $x_ratio = $max_width / $width;
        $y_ratio = $max_height / $height;

        if (($width <= $max_width) && ($height <= $max_height)) {
            $tn_width = $width;
            $tn_height = $height;
        } elseif (($x_ratio * $height) < $max_height) {
            $tn_height = ceil($x_ratio * $height);
            $tn_width = $max_width;
        } else {
            $tn_width = ceil($y_ratio * $width);
            $tn_height = $max_height;
        }

        $tmp = imagecreatetruecolor($tn_width, $tn_height);

        /* Check if this image is PNG or GIF, then set if Transparent*/
        if (($image_type == 1) or ($image_type == 3)) {
            imagealphablending($tmp, false);
            imagesavealpha($tmp, true);
            $transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
            imagefilledrectangle($tmp, 0, 0, $tn_width, $tn_height, $transparent);
        }
        imagecopyresampled($tmp, $src, 0, 0, 0, 0, $tn_width, $tn_height, $width, $height);

        /*
         * imageXXX() only has two options, save as a file, or send to the browser.
         * It does not provide you the oppurtunity to manipulate the final GIF/JPG/PNG file stream
         * So I start the output buffering, use imageXXX() to output the data stream to the browser,
         * get the contents of the stream, and use clean to silently discard the buffered contents.
         */
        ob_start();

        switch ($image_type) {
            case 1:
                imagegif($tmp);
                break;
            case 2:
                imagejpeg($tmp, null, 100);
                break; // best quality
            case 3:
                imagepng($tmp, null, 0);
                break; // no compression
            default:
                echo '';
                break;
        }

        $final_image = ob_get_contents();

        ob_end_clean();

        return $final_image;
    }

    public function isImage(string $mimeType): bool
    {
        if (str_starts_with($mimeType, 'image/')) {
            return true;
        }
        return false;
    }

    public function getFileName(UploadedFile $file): string|false
    {
        return hash_file('md5', $file->getPathname()) . '.' . $file->guessExtension();
    }

    /**
     * @return File
     */
    public function upload(UploadedFile $file, string $path): File
    {
        $fileEntity = new File();

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $uniqueFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        $fileEntity->setUniqueFilename($uniqueFilename);
        $fileEntity->setOriginalName($originalFilename);
        $fileEntity->setType($file->getMimeType());
        $fileEntity->setSize($file->getSize());

        if ($this->isImage($file->getMimeType())) {
            [$width, $height] = getimagesize($file->getPathname());
            $fileEntity->setWidth($width);
            $fileEntity->setHeight($height);
        }


        $targetDir =  $this->targetDirectory;
        $webDir =  $this->webDirectory;

        if(!empty($path)) {
            $path = ltrim($path, '/');
            $targetDir = "{$this->targetDirectory}/{$path}";
            $webDir = "{$webDir}/{$path}";
        }

        try {
            if(!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $file->move($targetDir, $uniqueFilename);
        } catch (FileException) {
            throw new Exception('Greshka pri kachvaneto!');
        }

        $fileEntity->setWebDir($webDir);

        $this->em->persist($fileEntity);
        $this->em->flush();

        return $fileEntity;
    }


    /**
     * @param File $file
     * @return array<mixed>
     */
    public function transformEntityToArray(File $file): array
    {
        $tmp = [
            'id' => $file->getId(),
            'name' => $file->getOriginalName(),
            'type' => $file->getType(),
            'uniqueFilename' => $file->getUniqueFilename(),
            'size' => $file->getSize(),
            'url' => $file->getFullUrl(),
        ];

        if ($this->isImage($file->getType())) {
            $tmp['url_optimized'] = $file->getOptimizedFullUrl(0, 0);
            $tmp['width'] = $file->getWidth();
            $tmp['height'] = $file->getHeight();
        }

        return $tmp;
    }

    public function removeFileFromFileSystem(File $file): void
    {
        $dir = $this->params->get('kernel.project_dir').'/public'.$file->getWebDir();

        if(file_exists($dir)) {
            $dirFiles = scandir($dir);
            foreach ($dirFiles as $dirFile) {
                if ($dirFile == $file->getUniqueFilename()) {
                    unlink($dir . '/' . $dirFile);
                }
            }
        }
        $dir = $this->params->get('kernel.project_dir').'/public'.$file->getWebDir().'/optimized';
        if(file_exists($dir)) {
            $dirFiles = scandir($dir);
            foreach ($dirFiles as $dirFile) {
                if (str_ends_with((string) $dirFile, $file->getUniqueFilename())) {
                    unlink($dir . '/' . $dirFile);
                }
            }
        }
    }

}
