<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CursoController extends AbstractController
{
    #[Route('/curso', name: 'curso')]
    public function index(): Response
    {
        return new Response('Página de Cursos');
    }
}