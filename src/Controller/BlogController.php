<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BlogController extends AbstractController
{
    #[Route(
        '/blog/{page<\d+>}',
        name: 'blog_list',
        //condition:"context.getMethod() in ['GET', 'HEAD'] and request.headers.get('User-Agent') matches '/PostmanRuntime/'"
        //condition:"request.headers.get('User-Agent') matches '%app.allowed_browsers%'"
        //condition: "params['id'] < 1000"
        condition: "service('route_checker').check(request)",
       // requirements: ['page' => '\d+'],
    )]
    public function list(int $page): Response
    {
        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
        ]);
    }

    #[Route('/blog/{page}', name: 'blog_show', priority: 2)]
    public function show($page): Response
    {
        // ...
        return new Response("Affichage de l'article de blog avec l'id : " . $page);
    }

    #[Route('/blog/{page}', name: 'blog_edit', methods: ['PUT'])]
    public function edit(int $page): Response
    {
        // ...
        return new Response("" . $page);
    }
}
