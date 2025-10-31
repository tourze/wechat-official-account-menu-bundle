<?php

namespace WechatOfficialAccountMenuBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;

/**
 * @extends ServiceEntityRepository<MenuButtonVersion>
 */
#[AsRepository(entityClass: MenuButtonVersion::class)]
final class MenuButtonVersionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuButtonVersion::class);
    }

    /**
     * 获取版本的根菜单.
     *
     * @return array<int, MenuButtonVersion>
     */
    public function findRootButtons(MenuVersion $version): array
    {
        $result = $this->createQueryBuilder('b')
            ->andWhere('b.version = :version')
            ->andWhere('b.parent IS NULL')
            ->setParameter('version', $version)
            ->orderBy('b.position', 'ASC')
            ->addOrderBy('b.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
        assert(is_array($result) && array_is_list($result));

        return array_values(array_filter($result, fn ($item): bool => $item instanceof MenuButtonVersion));
    }

    /**
     * 获取下一个排序位置.
     */
    public function getNextPosition(MenuVersion $version, ?MenuButtonVersion $parent = null): int
    {
        $qb = $this->createQueryBuilder('b')
            ->select('MAX(b.position) as maxPos')
            ->andWhere('b.version = :version')
            ->setParameter('version', $version)
        ;

        if (null === $parent) {
            $qb->andWhere('b.parent IS NULL');
        } else {
            $qb->andWhere('b.parent = :parent')
                ->setParameter('parent', $parent)
            ;
        }

        $result = $qb->getQuery()->getSingleScalarResult();

        return null !== $result ? (int) $result + 1 : 0;
    }

    /**
     * 批量更新排序.
     *
     * @param array<string, int> $positions 键为按钮ID，值为新位置
     */
    public function updatePositions(array $positions): void
    {
        foreach ($positions as $buttonId => $position) {
            $this->createQueryBuilder('b')
                ->update()
                ->set('b.position', ':position')
                ->where('b.id = :id')
                ->setParameter('position', $position)
                ->setParameter('id', $buttonId)
                ->getQuery()
                ->execute()
            ;
        }
    }

    public function save(MenuButtonVersion $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MenuButtonVersion $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
