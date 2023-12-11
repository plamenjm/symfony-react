<?php // $ bin/console make:controller WebController

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebController extends AbstractController
{
    #[Route('/spa', name: 'web_spa')]
    public function spa(): Response
    {
        return $this->render('web/spa.html.twig', [
            'controller_name' => 'WebController.spa',
        ]);
    }
}
