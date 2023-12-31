<?php // $ bin/console make:controller WebController

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class WebController extends AbstractController
{
    //#[Route('/', name: 'homepage')] // to-do
    #[Route('/index', name: '/index')]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route('/db', name: '/db')]
    public function db(): Response
    {
        return $this->render('db.html.twig');
    }

    #[Route('/spa/{page}', name: '/spa', defaults: ['page' => ''])]
    public function spa(Request $request): Response
    {
        $route = $request->attributes->get('_route');
        $url = $this->generateUrl($route, [], UrlGeneratorInterface::ABSOLUTE_URL);
        $urlApi = implode('/', array_slice(explode('/', $url), 0, 3))
            . \App\Constant::APP_PATH_API;

        return $this->render('spa.html.twig', [
            'route' => $route, //'path' => $this->generateUrl($routeName),
            'urlApi' => $urlApi,
        ]);
    }
}
