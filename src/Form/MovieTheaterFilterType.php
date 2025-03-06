<?php

namespace App\Form;

use App\Entity\MovieTheater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MovieTheaterFilterType extends AbstractType
{

    public function __construct(private EntityManagerInterface $entityManager) {}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $movieTheaters = $this->entityManager->getRepository(MovieTheater::class)->findAll();
        $choices = array_combine(
            array_map(fn($movieTheater) => $movieTheater->getTheaterName(), $movieTheaters),
            array_map(fn($movieTheater) => $movieTheater->getId(), $movieTheaters)
        );

        $resolver->setDefaults([
            'choices' => $choices
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class; 
    }
}
