<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class BlogController extends AbstractController
{
    public function __construct(
        private UrlGeneratorInterface $router,
    ) {}

     #[Route("/blog/articles", name: "blog_articles")]
    public function listArticles(int $page): Response
    {
        return new Response("Liste des articles de blog - page ");
    }

    #[Route(
        '/blog/{page<\d+>?1}',
        // '/blog/{_locale}/search.{_format}',
        name: 'blog_list',
        //condition:"context.getMethod() in ['GET', 'HEAD'] and request.headers.get('User-Agent') matches '/PostmanRuntime/'"
        //condition:"request.headers.get('User-Agent') matches '%app.allowed_browsers%'"
        //condition: "params['id'] < 1000"
        condition: "service('route_checker').check(request)",
        requirements: [
            'page' => '\d+',
            '_locale' => 'en|fr',
            '_format' => 'html|json',
        ],
        defaults: ['page' => 1, 'title' => 'Hello world'],
        schemes: ['https'],
    )]
    public function list(int $page): Response
    {
        $this->generateUrl('blog', ['page' => 2, 'category' => 'Symfony']);
        // the 'blog' route only defines the 'page' parameter; the generated URL is:
        // /blog/2?category=Symfony

        // generate a URL with no route arguments
        $signUpPage = $this->generateUrl('sign_up'); // ou $this->router->generate('sign_up');
        // generate a URL with route arguments
        // $userProfilePage = $this->generateUrl('user_profile', [
        //     'username' => $user->getUserIdentifier(),
        // ]);
        // generated URLs are "absolute paths" by default. Pass a third optional
        // argument to generate different URLs (e.g. an "absolute URL")
        $signUpPage = $this->generateUrl('sign_up', [], UrlGeneratorInterface::ABSOLUTE_URL);
        // when a route is localized, Symfony uses by default the current request locale
        // pass a different '_locale' value if you want to set the locale explicitly
        $signUpPageInDutch = $this->generateUrl('sign_up', ['_locale' => 'nl']);

        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
        ]);
    }

    #[Route(
        // path: [
        //     'en' => '/about-us',
        //     'nl' => '/over-ons'
        // ],
        '/blog/{page}/{token}',
        name: 'blog_show',
        priority: 2,
        defaults: ['page' => 1, 'title' => 'Hello world'],
        requirements: ['token' => '.+'],
        // stateless: true,
        // host: 'm.exemple.com',
        // host: '{subdomain}.exemple.com',
        // host: '{subdomain<m|mobile>?m}.example.com',
        // defaults: ['subdomain' => 'm'],
        // requirements: ['subdomain' => 'm|www|api'],
    )]
    public function show(Request $request, $page, string $title): Response
    {
        $routeName = $request->attributes->get('_route');
        $routeParameters = $request->attributes->get('_route_params');
        $allAttributes = $request->attributes->all();
        // ...
        return new Response("Affichage de l'article de blog avec l'id : " . $page . " et le titre : " . $title);
    }

    #[Route('/twig', name: 'blog_edit')]
    public function edit(): Response
    {
        // ...
        $users = [
            ['name' => 'Alice', 'age' => 30],
            ['name' => 'Bob', 'age' => 25],
            ['name' => 'Charlie', 'age' => 35],
        ];

        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'users' => $users,
        ]);
    }

    #[Route('/users', name: 'blog_users')]
    public function users(): Response
    {
        // ...
        $users = [
            ['name' => 'Alice', 'age' => 30],
            ['name' => 'Bob', 'age' => 25],
            ['name' => 'Charlie', 'age' => 35],
        ];
        
        return $this->render('blog/_user_profile.html.twig', [
            'users' => $users,
        ]);
    }
}
