<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

class DashboardController extends AbstractController
{
    #[Route(path: '/', name: 'dashboard')]
    public function index(RouterInterface $router): Response
    {
        return $this->render('dashboard/index.html.twig', []);
    }


    // #[Route(path: '/test', name: 'dashboardtest')]
    // public function test(): JsonResponse
    // {

    //     return $this->json(['test'=>'neshto si']);
    ////dd('s');
    //        $r = new RouterCacheWarmer($container);
    //        $cacheDir = $this->getParameter('kernel.cache_dir').'\t';
    ////        dd($cacheDir);
    //        $t = $r->warmUp($cacheDir);
    //        dd($t);
    //        dd('2');
    //        return $this->render(dashboard/index.html.twig', [
    //        ]);
    // }
    #[Route(path: '/api/test', name: 'dashboardtest_api')]
    public function apitest(): JsonResponse
    {

        return $this->json(['test' => 'api neshto si']);
    }
}
