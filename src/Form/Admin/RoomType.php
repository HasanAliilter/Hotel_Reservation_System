<?php

namespace App\Form\Admin;

use App\Entity\Admin\Room;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RoomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('image', FileType::class, [
                'label' => 'Hotel Gallery Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/*', // All image types
                        ],
                        'mimeTypesMessage' => 'Please upload a valid Image File',
                    ])
                ],
            ])
            ->add('price')
            ->add('number')
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'True' => 'True',
                    'False' => 'False'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Room::class,
        ]);
    }
}
