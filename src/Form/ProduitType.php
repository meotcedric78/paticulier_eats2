<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Produit;
use App\Entity\SousCategorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['ajouter']==true) {
            $builder
                ->add('nom', TextType::class, [
                    'required' => false,
                    'label' => false,
                    'attr' => [
                        'placeholder' => "Veuillez saisir le nom du produit",
                        'class' => 'inputNameProduit'
                    ]
                ])
                ->add('prix', NumberType::class, [
                    'required' => false,
                    'attr' => [
                        'placeholder' => "Veuillez saisir le prix du produit"
                    ]
                ])
                ->add('description', TextType::class, [
                    'required' => false,
                    'attr' => [
                        'placeholder' => "Saisir la description du produit"
                    ]
                ])
                ->add('image', FileType::class, [
                    "required" => false,
                    'constraints' => [
                        new File([
                            'mimeTypes' => [
                                "image/png",
                                "image/jpg",
                                "image/jpeg",
                            ],
                            'mimeTypesMessage' => "Extensions Autoriséés : PNG JPG JPEG"
                        ])
                    ]
                ])


                ->add('categorie', EntityType::class, [
                    "class" => Categorie::class,
                    "choice_label" => "nom",

                ])
                ->add('sousCategories', EntityType::class, [
                    "class" => SousCategorie::class,
                    "choice_label" => "nom",
                    "multiple"=>true,
                    'attr' => [
                        'class' => "select2",
                        'data-placeholder' => "Sélectionnez une ou des sous-catégories"
                    ]
                ]);
        }elseif ($options['modifier']==true){

            $builder
                ->add('nom', TextType::class, [
                    'required' => false,
                    'label' => false,
                    'attr' => [
                        'placeholder' => "Veuillez saisir le nom du produit",
                        'class' => 'inputNameProduit'
                    ]
                ])
                ->add('prix', NumberType::class, [
                    'required' => false,
                    'attr' => [
                        'placeholder' => "Veuillez saisir le prix du produit"
                    ]
                ])
                ->add('description', TextType::class, [
                    'required' => false,
                    'attr' => [
                        'placeholder' => "Saisir la description du produit"
                    ]
                ])
                ->add('imageFiles', FileType::class, [
                    "required" => false,
                    'constraints' => [
                        new File([
                            'mimeTypes' => [
                                "image/png",
                                "image/jpg",
                                "image/jpeg",
                            ],
                            'mimeTypesMessage' => "Extensions Autoriséés : PNG JPG JPEG"
                        ])
                    ]
                ])


                ->add('categorie', EntityType::class, [
                    "class" => Categorie::class,
                    "choice_label" => "nom",

                ])
                ->add('sousCategories', EntityType::class, [
                    "class" => SousCategorie::class,
                    "choice_label" => "nom",
                    "multiple"=>true,
                    'attr' => [
                        'class' => "select2",
                        'data-placeholder' => "Sélectionnez une ou des sous-catégories",
                    ]


                ]);

        }











    }




    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
            'ajouter'=>false,
            'modifier'=>false,
        ]);

    }
}
