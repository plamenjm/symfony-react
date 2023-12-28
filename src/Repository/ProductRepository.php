<?php // $ bin/console make:entity Product

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @return Product[] Returns an array of Product objects
     */
    public function findByNameDQL(string $name): array
    {
        return $this->getEntityManager()->createQuery(
            'SELECT p, c FROM App\Entity\Product p'
            . ' INNER JOIN p.category c'
            . ' WHERE p.name like :name'
            . ' ORDER BY p.id ASC')
            //->setMaxResults(10)->setFirstResult(0)
            //->setFetchMode('App\Entity\Product', 'category', \Doctrine\ORM\Mapping\ClassMetadataInfo::FETCH_EAGER)
            ->setParameter('name', '%' . $name . '%')
            ->getArrayResult();
    }

    /**
     * @return Product[] Returns an array of Product objects
     */
    public function findByPriceQB(int $price): array
    {
        return $this->createQueryBuilder('p')
            ->select(['p', 'c'])
            ->innerJoin('p.category', 'c')
            ->where('p.price = :price')
            ->orderBy('p.id', 'ASC')
            //->setMaxResults(10)->setFirstResult(0)
            ->setParameter('price', $price)->getQuery()
            ->getArrayResult();
    }

    public function findByDescriptionSQL(string $description): array
    {
        /** @noinspection SqlDialectInspection SqlNoDataSourceInspection */
        $res = $this->getEntityManager()->getConnection()->executeQuery(
            'SELECT * FROM product p WHERE p.description like :description ORDER BY p.price ASC',
            ['description' => '%' . $description . '%'])
            ->fetchAllAssociative();
        return $res;
    }

    public function findByDescriptionNative(string $description): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(Product::class, 'p');
        $rsm->addFieldResult('p', 'id', 'id');
        $rsm->addMetaResult('p', 'category_id', 'category_id');
        $rsm->addMetaResult('p', 'name', 'name');
        $rsm->addMetaResult('p', 'price', 'price');
        $rsm->addMetaResult('p', 'description', 'description');
        /** @noinspection SqlDialectInspection SqlNoDataSourceInspection */
        return $this->getEntityManager()->createNativeQuery(
            'SELECT * FROM product p WHERE p.description like ? ORDER BY p.price ASC',
            $rsm)
            ->setParameter(1, '%' . $description . '%')
            ->getArrayResult();
    }
}
