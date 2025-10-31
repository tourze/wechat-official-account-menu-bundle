<?php

namespace WechatOfficialAccountMenuBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuVersionStatus;

/**
 * @extends ServiceEntityRepository<MenuVersion>
 */
#[AsRepository(entityClass: MenuVersion::class)]
final class MenuVersionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuVersion::class);
    }

    /**
     * 获取账号的当前发布版本.
     */
    public function findCurrentPublishedVersion(Account $account): ?MenuVersion
    {
        $result = $this->createQueryBuilder('v')
            ->andWhere('v.account = :account')
            ->andWhere('v.status = :status')
            ->setParameter('account', $account)
            ->setParameter('status', MenuVersionStatus::PUBLISHED)
            ->orderBy('v.publishedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        assert($result instanceof MenuVersion || null === $result);

        return $result;
    }

    /**
     * 获取账号的最新草稿版本.
     */
    public function findLatestDraftVersion(Account $account): ?MenuVersion
    {
        $result = $this->createQueryBuilder('v')
            ->andWhere('v.account = :account')
            ->andWhere('v.status = :status')
            ->setParameter('account', $account)
            ->setParameter('status', MenuVersionStatus::DRAFT)
            ->orderBy('v.createTime', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        assert($result instanceof MenuVersion || null === $result);

        return $result;
    }

    /**
     * 获取账号的所有版本.
     *
     * @return array<int, MenuVersion>
     */
    public function findByAccount(Account $account): array
    {
        $result = $this->createQueryBuilder('v')
            ->andWhere('v.account = :account')
            ->setParameter('account', $account)
            ->orderBy('v.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
        assert(is_array($result) && array_is_list($result));

        return array_values(array_filter($result, fn ($item): bool => $item instanceof MenuVersion));
    }

    /**
     * 生成新的版本号.
     */
    public function generateNextVersionNumber(Account $account): string
    {
        $latest = $this->createQueryBuilder('v')
            ->select('v.version')
            ->andWhere('v.account = :account')
            ->setParameter('account', $account)
            ->orderBy('v.createTime', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (null === $latest) {
            return '1.0.0';
        }

        assert(is_array($latest) && isset($latest['version']) && is_string($latest['version']));

        return MenuVersion::generateNextVersion($latest['version']);
    }

    /**
     * 归档旧的已发布版本.
     */
    public function archiveOldPublishedVersions(Account $account, MenuVersion $exceptVersion): int
    {
        $result = $this->createQueryBuilder('v')
            ->update()
            ->set('v.status', ':newStatus')
            ->andWhere('v.account = :account')
            ->andWhere('v.status = :oldStatus')
            ->andWhere('v.id != :exceptId')
            ->setParameter('newStatus', MenuVersionStatus::ARCHIVED)
            ->setParameter('account', $account)
            ->setParameter('oldStatus', MenuVersionStatus::PUBLISHED)
            ->setParameter('exceptId', $exceptVersion->getId())
            ->getQuery()
            ->execute()
        ;
        assert(is_int($result));

        return $result;
    }

    /**
     * 检查版本号是否已存在.
     */
    public function versionExists(Account $account, string $version): bool
    {
        return (bool) $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->andWhere('v.account = :account')
            ->andWhere('v.version = :version')
            ->setParameter('account', $account)
            ->setParameter('version', $version)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function save(MenuVersion $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MenuVersion $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
