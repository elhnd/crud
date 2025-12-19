<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\Type\TaskType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
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
}
