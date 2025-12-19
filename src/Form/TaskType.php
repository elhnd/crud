<?php

namespace App\Form\Type;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('task', /*TextType::class*/)
            /*->add('task', null, ['attr' => ['maxlength' => 4]])*/
            ->add('dueDate', null, [
                'required' => $options['require_due_date'],
                'label' => 'Du Date',
            ])
            ->add('agreeTerms', CheckboxType::class, ['mapped' => false])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
            'require_due_date' => false,
        ]);

        $resolver->setAllowedTypes('require_due_date', 'bool');
    }
}