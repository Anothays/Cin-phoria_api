<?php

namespace App\Controller\Admin\Filter;

use App\Form\VersionFilterType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;

class VersionFilter implements FilterInterface
{
  use FilterTrait;

  public static function new(string $propertyName, $label = null): self
  {
    return (new self())
      ->setFilterFqcn(__CLASS__)
      ->setProperty($propertyName)
      ->setLabel($label)
      ->setFormType(VersionFilterType::class)
    ;
  }

  public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
  {

    if ('VO' === $filterDataDto->getValue()) {
      $queryBuilder
        ->andWhere(sprintf("%s.%s = 'VO'", $filterDataDto->getEntityAlias(), $filterDataDto->getProperty() ));
    } else {
      $queryBuilder
        ->andWhere(sprintf("%s.%s = 'VF'", $filterDataDto->getEntityAlias(), $filterDataDto->getProperty() ));
    }
    
  }
}