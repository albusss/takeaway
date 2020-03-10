<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Uuid;

class PostMessageApiForm extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     *
     * @return \Symfony\Component\Form\FormInterface|void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): FormInterface
    {
        $builder->add('delivery_time', DateTimeType::class, [
            'widget' => 'single_text',
            'format' => 'yyyy-MM-dd HH:mm:ss',
            'constraints' => [
                new NotBlank(),
            ],
        ]);
        $builder->add('restaurant_title', TextType::class, [
            'constraints' => [
                new NotBlank(),
                new Length(['max' => 255]),
            ],
        ]);
        $builder->add('idempotency_key', TextType::class, [
            'constraints' => [
                new NotBlank(),
                new Uuid(),
            ],
        ]);
        $builder->add('phone', TextType::class, [
            'constraints' => [
                new NotBlank(),
                new Regex("/^\d{11}$/"),
            ],
        ]);

        return $builder->getForm();
    }
}
