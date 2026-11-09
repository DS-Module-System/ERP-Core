<?php

namespace App\Controller\Core;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '')]
class CoreInformationController extends AbstractController
{
    #[Route(path: '/information', name: 'core_information')]
    public function index(): Response
    {
        return $this->render('core/information/index.html.twig', [
            'company_name' => 'DS Module System',
            'short_product_name' => 'DS ERP',
            'product_name' => 'DevScale Enterprise Resource Planning',
            'website' => 'https://www.dsmodulesystem.com',
            'office_email' => 'support@dsmodulesystem.com',
            'support_email' => 'support@dsmodulesystem.com',
            'documenttation_link' => 'https://www.dsmodulesystem.com/documentation',
        ]);
    }
}
