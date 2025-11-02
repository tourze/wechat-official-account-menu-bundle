<?php

namespace WechatOfficialAccountMenuBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;

/**
 * @extends ServiceEntityRepository<MenuButton>
 */
#[AsRepository(entityClass: MenuButton::class)]
class MenuButtonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuButton::class);
    }

    /**
     * 获取账号的根菜单（已排序）.
     *
     * @return array<int, MenuButton>
     * @phpstan-return list<MenuButton>
     */
    public function findRootMenusByAccount(Account $account): array
    {
        $result = $this->createQueryBuilder('m')
            ->andWhere('m.account = :account')
            ->andWhere('m.parent IS NULL')
            ->andWhere('m.enabled = :enabled')
            ->setParameter('account', $account)
            ->setParameter('enabled', true)
            ->orderBy('m.position', 'ASC')
            ->addOrderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
        assert(is_array($result));

        /** @var list<MenuButton> */
        return $result;
    }

    /**
     * 获取下一个排序位置.
     */
    public function getNextPosition(Account $account, ?MenuButton $parent = null): int
    {
        $qb = $this->createQueryBuilder('m')
            ->select('MAX(m.position) as maxPos')
            ->andWhere('m.account = :account')
            ->setParameter('account', $account)
        ;

        if (null === $parent) {
            $qb->andWhere('m.parent IS NULL');
        } else {
            $qb->andWhere('m.parent = :parent')
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
            $this->createQueryBuilder('m')
                ->update()
                ->set('m.position', ':position')
                ->where('m.id = :id')
                ->setParameter('position', $position)
                ->setParameter('id', $buttonId)
                ->getQuery()
                ->execute()
            ;
        }
    }

    /**
     * 获取账号的所有菜单（包含禁用的）.
     *
     * @return array<int, MenuButton>
     * @phpstan-return list<MenuButton>
     */
    public function findAllByAccount(Account $account): array
    {
        $result = $this->createQueryBuilder('m')
            ->andWhere('m.account = :account')
            ->setParameter('account', $account)
            ->orderBy('m.position', 'ASC')
            ->addOrderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
        assert(is_array($result));

        /** @var list<MenuButton> */
        return $result;
    }

    /**
     * 获取所有有菜单的账号.
     *
     * @return array<int, Account>
     * @phpstan-return list<Account>
     */
    public function findAccountsWithMenus(): array
    {
        // 由于需要查询不同的实体（Account），必须使用EntityManager的QueryBuilder
        $qb = $this->getEntityManager()->createQueryBuilder();

        $result = $qb->select('DISTINCT a')
            ->from(Account::class, 'a')
            ->innerJoin(MenuButton::class, 'm', 'WITH', 'm.account = a')
            ->getQuery()
            ->getResult()
        ;
        assert(is_array($result));

        /** @var list<Account> */
        return $result;
    }

    public function save(MenuButton $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MenuButton $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
