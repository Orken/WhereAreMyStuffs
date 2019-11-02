<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * @method Box|null find($id, $lockMode = null, $lockVersion = null)
 * @method Box|null findOneBy(array $criteria, array $orderBy = null)
 * @method Box[]    findAll()
 * @method Box[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends NestedTreeRepository
{

    public function __construct(EntityManagerInterface $manager)
    {
        parent::__construct($manager, $manager->getClassMetadata(Item::class));
    }
    public function getWithSearchQueryBuilder(?string $q): QueryBuilder
    {
        $qb = $this->createQueryBuilder('i');

        if ($q) {
            $qb->andWhere('i.name LIKE :q OR i.comment LIKE :q')
                ->setParameter('q', '%' . $q . '%');
        }

        return $qb->orderBy('i.lvl', 'ASC');//->orderBy('i.createdAt', 'DESC');
    }

    public function getBySlug(string $slug){
        $qb = $this->createQueryBuilder('i');

        return $qb->andWhere('i.slug=:slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

}
