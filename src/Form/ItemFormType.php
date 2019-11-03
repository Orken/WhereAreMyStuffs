<?php

namespace App\Form;

use App\Entity\Item;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Entity\File;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ItemFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name')
                ->add('comment')
//                ->add('imageName')
                ->add('imageFile', VichImageType::class, [
                        'required' => false,
                        'imagine_pattern' => 'thumb64x64',
                        'attr' => [
                                'accept' => "image/*",
                                'camera' => true

                        ]
                ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                'data_class' => Item::class
        ]);
    }
}
