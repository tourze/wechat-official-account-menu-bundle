<?php

namespace WechatOfficialAccountMenuBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuVersionStatus;
use WechatOfficialAccountMenuBundle\Repository\MenuVersionRepository;

/**
 * @internal
 */
#[CoversClass(MenuVersionRepository::class)]
#[RunTestsInSeparateProcesses]
final class MenuVersionRepositoryTest extends AbstractRepositoryTestCase
{
    private MenuVersionRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(MenuVersionRepository::class);
    }

    public function testRepositoryCanFindByAccount(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->flush();

        $results = $this->repository->findBy(['account' => $account]);
        $this->assertCount(1, $results);
        $this->assertSame($menuVersion, $results[0]);
    }

    public function testFindOneByWithOrderBy(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());

        $menuVersion1 = new MenuVersion();
        $menuVersion1->setAccount($account);
        $menuVersion1->setVersion('v2.0.0');
        $menuVersion1->setStatus(MenuVersionStatus::DRAFT);

        $menuVersion2 = new MenuVersion();
        $menuVersion2->setAccount($account);
        $menuVersion2->setVersion('v1.0.0');
        $menuVersion2->setStatus(MenuVersionStatus::DRAFT);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion1);
        self::getEntityManager()->flush();

        sleep(1);
        self::getEntityManager()->persist($menuVersion2);
        self::getEntityManager()->flush();

        $result = $this->repository->findOneBy([
            'account' => $account,
            'status' => MenuVersionStatus::DRAFT,
        ], ['version' => 'ASC']);

        // 验证找到了结果
        $this->assertInstanceOf(MenuVersion::class, $result, 'Should find a menu version');

        // 验证排序工作正确 - 按版本号字母排序，v1.0.0 应该在 v2.0.0 前面
        $this->assertEquals('v1.0.0', $result->getVersion(), 'Should return the first version alphabetically');
        $this->assertSame($menuVersion2, $result, 'Should return menuVersion2 (v1.0.0)');
    }

    public function testRepositoryCanFindByStatus(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setStatus(MenuVersionStatus::PUBLISHED);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->flush();

        $results = $this->repository->findBy(['status' => MenuVersionStatus::PUBLISHED]);
        $this->assertGreaterThanOrEqual(1, count($results));
    }

    public function testRepositoryCanFindByNullableFields(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setDescription(null);
        $menuVersion->setPublishedAt(null);
        $menuVersion->setPublishedBy(null);
        $menuVersion->setCopiedFrom(null);
        $menuVersion->setMenuSnapshot(null);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->flush();

        // Test IS NULL queries for all nullable fields
        $resultsDescription = $this->repository->findBy(['description' => null]);
        $this->assertGreaterThanOrEqual(1, count($resultsDescription));

        $resultsPublishedAt = $this->repository->findBy(['publishedAt' => null]);
        $this->assertGreaterThanOrEqual(1, count($resultsPublishedAt));

        $resultsPublishedBy = $this->repository->findBy(['publishedBy' => null]);
        $this->assertGreaterThanOrEqual(1, count($resultsPublishedBy));

        $resultsCopiedFrom = $this->repository->findBy(['copiedFrom' => null]);
        $this->assertGreaterThanOrEqual(1, count($resultsCopiedFrom));

        $resultsMenuSnapshot = $this->repository->findBy(['menuSnapshot' => null]);
        $this->assertGreaterThanOrEqual(1, count($resultsMenuSnapshot));
    }

    public function testRepositoryCanCountByNullableFields(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setDescription(null);
        $menuVersion->setPublishedAt(null);
        $menuVersion->setPublishedBy(null);
        $menuVersion->setCopiedFrom(null);
        $menuVersion->setMenuSnapshot(null);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->flush();

        // Test count IS NULL queries for all nullable fields
        $countDescription = $this->repository->count(['description' => null]);
        $this->assertGreaterThanOrEqual(1, $countDescription);

        $countPublishedAt = $this->repository->count(['publishedAt' => null]);
        $this->assertGreaterThanOrEqual(1, $countPublishedAt);

        $countPublishedBy = $this->repository->count(['publishedBy' => null]);
        $this->assertGreaterThanOrEqual(1, $countPublishedBy);

        $countCopiedFrom = $this->repository->count(['copiedFrom' => null]);
        $this->assertGreaterThanOrEqual(1, $countCopiedFrom);

        $countMenuSnapshot = $this->repository->count(['menuSnapshot' => null]);
        $this->assertGreaterThanOrEqual(1, $countMenuSnapshot);
    }

    public function testRepositoryCanFindWithJoins(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());

        $sourceVersion = new MenuVersion();
        $sourceVersion->setAccount($account);
        $sourceVersion->setVersion('1.0.0');
        $sourceVersion->setStatus(MenuVersionStatus::PUBLISHED);

        $copiedVersion = new MenuVersion();
        $copiedVersion->setAccount($account);
        $copiedVersion->setVersion('1.0.1');
        $copiedVersion->setCopiedFrom($sourceVersion);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($sourceVersion);
        self::getEntityManager()->persist($copiedVersion);
        self::getEntityManager()->flush();

        // Test join query with account
        $qb = $this->repository->createQueryBuilder('mv');
        $qb->innerJoin('mv.account', 'a')
            ->where('a.id = :accountId')
            ->setParameter('accountId', $account->getId())
        ;

        $results = $qb->getQuery()->getResult();
        $this->assertIsArray($results);
        $this->assertCount(2, $results);

        // Test join query with copiedFrom
        $qb2 = $this->repository->createQueryBuilder('mv');
        $qb2->innerJoin('mv.copiedFrom', 'cf')
            ->where('cf.id = :sourceId')
            ->setParameter('sourceId', $sourceVersion->getId())
        ;

        $results2 = $qb2->getQuery()->getResult();
        $this->assertIsArray($results2);
        $this->assertCount(1, $results2);
        $this->assertSame($copiedVersion, $results2[0]);
    }

    public function testCountWithJoinQueries(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());

        $sourceVersion = new MenuVersion();
        $sourceVersion->setAccount($account);
        $sourceVersion->setVersion('1.0.0');
        $sourceVersion->setStatus(MenuVersionStatus::PUBLISHED);

        $copiedVersion = new MenuVersion();
        $copiedVersion->setAccount($account);
        $copiedVersion->setVersion('1.0.1');
        $copiedVersion->setCopiedFrom($sourceVersion);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($sourceVersion);
        self::getEntityManager()->persist($copiedVersion);
        self::getEntityManager()->flush();

        // Test count with join query on account
        $qb = $this->repository->createQueryBuilder('mv');
        $qb->select('COUNT(mv.id)')
            ->innerJoin('mv.account', 'a')
            ->where('a.id = :accountId')
            ->setParameter('accountId', $account->getId())
        ;

        $count = $qb->getQuery()->getSingleScalarResult();
        $this->assertIsNumeric($count);
        $this->assertSame(2, (int) $count);

        // Test count with join query on copiedFrom
        $qb2 = $this->repository->createQueryBuilder('mv');
        $qb2->select('COUNT(mv.id)')
            ->innerJoin('mv.copiedFrom', 'cf')
            ->where('cf.id = :sourceId')
            ->setParameter('sourceId', $sourceVersion->getId())
        ;

        $count2 = $qb2->getQuery()->getSingleScalarResult();
        $this->assertIsNumeric($count2);
        $this->assertSame(1, (int) $count2);
    }

    public function testFindLatestDraftVersion(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion1 = new MenuVersion();
        $menuVersion1->setAccount($account);
        $menuVersion1->setVersion('1.0.0');
        $menuVersion1->setStatus(MenuVersionStatus::DRAFT);

        $menuVersion2 = new MenuVersion();
        $menuVersion2->setAccount($account);
        $menuVersion2->setVersion('1.0.1');
        $menuVersion2->setStatus(MenuVersionStatus::DRAFT);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion1);
        self::getEntityManager()->flush();

        // Ensure second version is created later
        sleep(1);
        self::getEntityManager()->persist($menuVersion2);
        self::getEntityManager()->flush();

        $result = $this->repository->findLatestDraftVersion($account);
        $this->assertSame($menuVersion2, $result);
    }

    public function testFindLatestDraftVersionWhenNoneExists(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $result = $this->repository->findLatestDraftVersion($account);
        $this->assertNull($result);
    }

    public function testGenerateNextVersionNumber(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        // Test with no existing versions
        $nextVersion = $this->repository->generateNextVersionNumber($account);
        $this->assertSame('1.0.0', $nextVersion);

        // Create a version and test next version
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');

        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->flush();

        $nextVersion = $this->repository->generateNextVersionNumber($account);
        $this->assertSame('1.0.1', $nextVersion);
    }

    public function testVersionExists(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->flush();

        $this->assertTrue($this->repository->versionExists($account, '1.0.0'));
        $this->assertFalse($this->repository->versionExists($account, '2.0.0'));
    }

    public function testSave(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $this->repository->save($menuVersion);

        $id = $menuVersion->getId();
        $this->assertIsInt($id, 'Menu version should have an ID after save');
        $found = $this->repository->find($id);
        $this->assertSame($menuVersion, $found);
    }

    public function testRemove(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->flush();

        $id = $menuVersion->getId();
        $this->repository->remove($menuVersion);

        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    public function testFindCurrentPublishedVersion(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion1 = new MenuVersion();
        $menuVersion1->setAccount($account);
        $menuVersion1->setVersion('1.0.0');
        $menuVersion1->setStatus(MenuVersionStatus::PUBLISHED);
        $menuVersion1->setPublishedAt(new \DateTimeImmutable('2023-01-01'));

        $menuVersion2 = new MenuVersion();
        $menuVersion2->setAccount($account);
        $menuVersion2->setVersion('1.0.1');
        $menuVersion2->setStatus(MenuVersionStatus::PUBLISHED);
        $menuVersion2->setPublishedAt(new \DateTimeImmutable('2023-01-02'));

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion1);
        self::getEntityManager()->persist($menuVersion2);
        self::getEntityManager()->flush();

        $result = $this->repository->findCurrentPublishedVersion($account);
        $this->assertSame($menuVersion2, $result);
    }

    public function testFindByAccount(): void
    {
        $account1 = new Account();
        $account1->setName('Test Account 1');
        $account1->setAppId('test_app_id_1');
        $account1->setAppSecret('test_app_secret_1');

        $account2 = new Account();
        $account2->setName('Test Account 2');
        $account2->setAppId('test_app_id_2');
        $account2->setAppSecret('test_app_secret_2');

        $menuVersion1 = new MenuVersion();
        $menuVersion1->setAccount($account1);
        $menuVersion1->setVersion('1.0.0');

        $menuVersion2 = new MenuVersion();
        $menuVersion2->setAccount($account2);
        $menuVersion2->setVersion('1.0.0');

        self::getEntityManager()->persist($account1);
        self::getEntityManager()->persist($account2);
        self::getEntityManager()->persist($menuVersion1);
        self::getEntityManager()->persist($menuVersion2);
        self::getEntityManager()->flush();

        $results = $this->repository->findByAccount($account1);
        $this->assertCount(1, $results);
        $this->assertSame($menuVersion1, $results[0]);
    }

    public function testArchiveOldPublishedVersions(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion1 = new MenuVersion();
        $menuVersion1->setAccount($account);
        $menuVersion1->setVersion('1.0.0');
        $menuVersion1->setStatus(MenuVersionStatus::PUBLISHED);

        $menuVersion2 = new MenuVersion();
        $menuVersion2->setAccount($account);
        $menuVersion2->setVersion('1.0.1');
        $menuVersion2->setStatus(MenuVersionStatus::PUBLISHED);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion1);
        self::getEntityManager()->persist($menuVersion2);
        self::getEntityManager()->flush();

        $archivedCount = $this->repository->archiveOldPublishedVersions($account, $menuVersion2);
        $this->assertSame(1, $archivedCount);

        self::getEntityManager()->refresh($menuVersion1);
        $this->assertSame(MenuVersionStatus::ARCHIVED, $menuVersion1->getStatus());

        self::getEntityManager()->refresh($menuVersion2);
        $this->assertSame(MenuVersionStatus::PUBLISHED, $menuVersion2->getStatus());
    }

    public function testFindOneByWithOrderByLogic(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());

        $menuVersion1 = new MenuVersion();
        $menuVersion1->setAccount($account);
        $menuVersion1->setVersion('2.0.0');
        $menuVersion1->setStatus(MenuVersionStatus::DRAFT);

        $menuVersion2 = new MenuVersion();
        $menuVersion2->setAccount($account);
        $menuVersion2->setVersion('1.0.0');
        $menuVersion2->setStatus(MenuVersionStatus::DRAFT);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion1);
        self::getEntityManager()->flush();

        sleep(1);
        self::getEntityManager()->persist($menuVersion2);
        self::getEntityManager()->flush();

        // Test findOneBy with ordering by version ASC
        $result = $this->repository->findOneBy([
            'account' => $account,
            'status' => MenuVersionStatus::DRAFT,
        ], ['version' => 'ASC']);
        $this->assertSame($menuVersion2, $result); // Version 1.0.0 should come first

        // Test findOneBy with ordering by version DESC
        $result2 = $this->repository->findOneBy([
            'account' => $account,
            'status' => MenuVersionStatus::DRAFT,
        ], ['version' => 'DESC']);
        $this->assertSame($menuVersion1, $result2); // Version 2.0.0 should come first
    }

    public function testFindOneByAssociationAccountShouldReturnMatchingEntity(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setStatus(MenuVersionStatus::DRAFT);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->flush();

        $result = $this->repository->findOneBy(['account' => $account]);
        $this->assertSame($menuVersion, $result);
    }

    public function testCountByAssociationAccountShouldReturnCorrectNumber(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());

        $menuVersion1 = new MenuVersion();
        $menuVersion1->setAccount($account);
        $menuVersion1->setVersion('1.0.0');
        $menuVersion1->setStatus(MenuVersionStatus::DRAFT);

        $menuVersion2 = new MenuVersion();
        $menuVersion2->setAccount($account);
        $menuVersion2->setVersion('1.0.1');
        $menuVersion2->setStatus(MenuVersionStatus::PUBLISHED);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion1);
        self::getEntityManager()->persist($menuVersion2);
        self::getEntityManager()->flush();

        $count = $this->repository->count(['account' => $account]);
        $this->assertSame(2, $count);
    }

    /**
     * @return MenuVersion
     */
    protected function createNewEntity(): object
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $entity = new MenuVersion();
        $entity->setAccount($account);
        $entity->setVersion('Test Version ' . uniqid());
        $entity->setStatus(MenuVersionStatus::DRAFT);

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<MenuVersion>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
