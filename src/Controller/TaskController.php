<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\Type\TaskType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TaskController extends AbstractController
{
    #[Route('/task/new', name: 'task_new')]
    public function new(Request $request, /*FormFactoryInterface $formFactory*/): Response
    {
        // creates a task object and initializes some data for this example

        $task = new Task();
        // $task->setTask('Write a blog post');
        // $task->setDueDate(new \DateTimeImmutable('tomorrow'));

        // $form = $this->createFormBuilder($task)
        //     ->setAction($this->generateUrl('task_new'))
        //     ->setMethod('GET')
        //     ->add('task', TextType::class)
        //     ->add('dueDate', DateType::class)
        //     ->add('save', SubmitType::class, ['label' => 'Create Task'])
        //     ->getForm();

        // To change the name of the form
        // $form = $formFactory->createNamed('my_name', TaskType::class, $task);

        $dueDateIsRequired = true;

        $form = $this->createForm(TaskType::class, $task, [
            'action' => $this->generateUrl('task_new'),
            'method' => 'GET',
            'require_due_date' => $dueDateIsRequired,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();

            return $this->redirectToRoute('task_success');
        }

        return $this->render('task/new.html.twig', [
            'form' => $form,
        ]);
    }

    public function testAsset(): Response
    {
        // Asset package config without versioning
        $package = new Package(new EmptyVersionStrategy());
        $package = new Package(new StaticVersionStrategy('v1')); // with path?v1 in the path

        $package = new Package(new StaticVersionStrategy('v1', '%s?version=%s')); // with path?version=v1 in the path

        $package = new Package(new StaticVersionStrategy('v1', '%2$s/%1$s')); // with /v1/path in the path  

        $package = new Package(new JsonManifestVersionStrategy(__DIR__ . '/rev-manifest.json')); // build/css/app.545454c2.css

        $httpClient = HttpClient::create();
        $manifestUrl = 'https://example.com/rev-manifest.json';
        $package = new Package(new JsonManifestVersionStrategy($manifestUrl, $httpClient));

        dump($package->getUrl('images/logo.png')); //Absolute path
        dump($package->getUrl('app.css')); // Relative path

        $pathPackage = new PathPackage('/static/images', new StaticVersionStrategy('v2'));

        $pathPackage = new PathPackage(
            '/static/images',
            new StaticVersionStrategy('v1'),
            new RequestStackContext($requestStack)
        );

        dump($pathPackage->getUrl('logo.png')); // /static/images/logo.png?v2
        dump($pathPackage->getUrl('/icon.png')); // /icon.png?v2

        return new Response('Task created successfully!');
    }
}
