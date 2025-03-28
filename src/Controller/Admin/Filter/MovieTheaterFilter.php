<?php

namespace App\Controller\Admin\Filter;

use App\Form\MovieTheaterFilterType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;

class MovieTheaterFilter implements FilterInterface
{
  use FilterTrait;

  public static function new(string $propertyName, $label = null): self
  {
    return (new self())
      ->setFilterFqcn(__CLASS__)
      ->setProperty($propertyName)
      ->setLabel($label)
      ->setFormType(MovieTheaterFilterType::class)
    ;
  }

  public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
  {
    $queryBuilder
      ->join("{$filterDataDto->getEntityAlias()}" . ".projectionRoom", 'room')
      ->join("room.movieTheater", 'movieTheater')
      ->andWhere('movieTheater.id = :movieTheaterId')
      ->setParameter('movieTheaterId', $filterDataDto->getValue());
    
  }

}