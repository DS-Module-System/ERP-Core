<?php

namespace App\Controller\Core;

use App\Entity\Core\File;
use App\Form\Core\FileApiForm;
use App\Service\Core\FileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/files')]
class FileController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator) {}

    #[Route(path: '/create', name: 'file_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, FileService $fileService): Response
    {

        $uploadedPath = $request->get('path', '');
        if ($uploadedPath) {
            $uploadedPath = urldecode((string) $uploadedPath);
        }

        /**
         * @var UploadedFile[] $files
         */
        $files = $request->files->get('files', []);
        $fileJson = [];

        if (isset($files[0])) {
            $fileEntity = $fileService->upload($files[0], $uploadedPath);
            return new JsonResponse([
                'fileinfo' => $fileService->transformEntityToArray($fileEntity),
            ]);
        }
        return new JsonResponse($fileJson);
    }

    #[Route(path: '/{id}', name: 'file_preview')]
    public function preview(int $id, FileService $fileService, EntityManagerInterface $em): Response
    {

        $file = $em->getRepository(File::class)->findOneBy(['id' => $id]);
        $prepareJson = [];
        if ($file !== null) {
            $tmp = $fileService->transformEntityToArray($file);
            $prepareJson[$tmp['id']] = $tmp;
        }
        return new JsonResponse($prepareJson);
    }

    #[Route(path: '/api-edit/{id}', name: 'file_api_edit')]
    public function edit(int $id, FileService $fileService, EntityManagerInterface $em): Response
    {

        $file = $em->getRepository(File::class)->find($id);
        if (!$file) {
            throw new \Exception('File not exist for edit!');
        }

        $form = $this->createForm(FileApiForm::class, $file);

        return $this->render('admin/file/api_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/api-save/{id}', name: 'file_api_save', methods: ['POST'])]
    public function apiSave(int $id, FileService $fileService, EntityManagerInterface $em, Request $request): Response
    {
        $requestData = $request->request->all();
        $statusCode = 201;
        $formData = [];
        if (isset($requestData['formData'])) {
            parse_str((string) $requestData['formData'], $formData);
        }

        $obj = $em->getRepository(File::class)->find($id);

        $form = $this->createForm(FileApiForm::class, $obj);

        if (!empty($formData)) {
            $form->submit($formData[$form->getName()]);
            if ($form->isValid()) {
                $em->persist($obj);
                $em->flush();
            } else {
                $statusCode = 400;
            }
        }

        return $this->render('admin/file/api_form.html.twig', [
            'form' => $form->createView(),
        ])->setStatusCode($statusCode);
    }

    #[Route(path: '/multiple/{ids}', name: 'file_multiple')]
    public function multiple(string $ids, FileService $fileService, EntityManagerInterface $em): Response
    {
        $ids = explode(',', (string) $ids);
        /**
         * @var File[] $files
         */
        $files = $em->getRepository(File::class)->findBy(['id' => $ids]);

        $orderFiles = [];
        if ($files) {
            foreach ($ids as $id) {
                foreach ($files as $file) {
                    if ($id == $file->getId()) {
                        $tmp = $fileService->transformEntityToArray($file);
                        $orderFiles[] = $tmp;
                        break;
                    }
                }
            }
        }
        return new JsonResponse($orderFiles);
    }



    #[Route(path: '/{fileId}/delete', name: 'admin_file_delete', methods: ['POST'])]
    public function delete(int $fileId, EntityManagerInterface $em): Response
    {
        $file = $em->getRepository(File::class)->find($fileId);
        if ($file) {
            $em->remove($file);
            $em->flush();
            return new JsonResponse(['delete' => 'success']);
        }

        return new JsonResponse(['delete' => 'error']);
    }

    #[Route(path: '/browse', name: 'file_browse')]
    public function browse(Request $request, EntityManagerInterface $em): Response
    {
        $files = $em->getRepository(File::class)->findBy([], ['id' => 'DESC']);

        if ($request->isXmlHttpRequest()) {
            return $this->render('file/browse_modal.html.twig', [
                'files' => $files,
            ]);
        }

        return $this->render('file/browse.html.twig', [
            'files' => $files,
        ]);
    }
}
