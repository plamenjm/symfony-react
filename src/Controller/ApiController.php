<?php # $ bin/console make:controller --no-template ApiController

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/api/params', name: 'api_params', methods: ['GET'])]
    public function params(): Response
    {
        return new Response(json_encode([
            'fullName' => 'ApiController'
        ]));
    }
}
