<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VersionFilterType extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => [
                "VF" => "VF",
                "VO" => "VO"
            ]
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class; 
    }
}
