<?php

namespace WechatOfficialAccountMenuBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Entity\MenuButtonVersion;
use WechatOfficialAccountMenuBundle\Entity\MenuVersion;
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Enum\MenuVersionStatus;
use WechatOfficialAccountMenuBundle\Repository\MenuButtonVersionRepository;

/**
 * @internal
 */
#[CoversClass(MenuButtonVersionRepository::class)]
#[RunTestsInSeparateProcesses]
final class MenuButtonVersionRepositoryTest extends AbstractRepositoryTestCase
{
    private MenuButtonVersionRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(MenuButtonVersionRepository::class);
    }

    public function testFindOneByWithNonExistentVersionShouldReturnNull(): void
    {
        $result = $this->repository->findOneBy(['name' => 'Non Existent Menu']);
        $this->assertNull($result);
    }

    public function testFindByWithNonExistentVersionShouldReturnEmptyArray(): void
    {
        $results = $this->repository->findBy(['name' => 'Non Existent Menu']);
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function testFindRootButtons(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setStatus(MenuVersionStatus::DRAFT);

        $rootButton = new MenuButtonVersion();
        $rootButton->setVersion($menuVersion);
        $rootButton->setName('Root Menu');
        $rootButton->setType(MenuType::CLICK);
        $rootButton->setClickKey('root_key');
        $rootButton->setPosition(0);
        $rootButton->setParent(null);

        $childButton = new MenuButtonVersion();
        $childButton->setVersion($menuVersion);
        $childButton->setName('Child Menu');
        $childButton->setType(MenuType::CLICK);
        $childButton->setClickKey('child_key');
        $childButton->setPosition(0);
        $childButton->setParent($rootButton);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->persist($rootButton);
        self::getEntityManager()->persist($childButton);
        self::getEntityManager()->flush();

        $results = $this->repository->findRootButtons($menuVersion);
        /** @var array<int, MenuButtonVersion> $results */
        $this->assertCount(1, $results);
        $this->assertSame($rootButton, $results[0]);
    }

    public function testGetNextPosition(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setStatus(MenuVersionStatus::DRAFT);

        $menuButtonVersion = new MenuButtonVersion();
        $menuButtonVersion->setVersion($menuVersion);
        $menuButtonVersion->setName('Menu');
        $menuButtonVersion->setType(MenuType::CLICK);
        $menuButtonVersion->setClickKey('key');
        $menuButtonVersion->setPosition(5);
        $menuButtonVersion->setParent(null);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->persist($menuButtonVersion);
        self::getEntityManager()->flush();

        $nextPosition = $this->repository->getNextPosition($menuVersion);
        $this->assertSame(6, $nextPosition);
    }

    public function testUpdatePositions(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setStatus(MenuVersionStatus::DRAFT);

        $menuButtonVersion1 = new MenuButtonVersion();
        $menuButtonVersion1->setVersion($menuVersion);
        $menuButtonVersion1->setName('Menu 1');
        $menuButtonVersion1->setType(MenuType::CLICK);
        $menuButtonVersion1->setClickKey('key_1');
        $menuButtonVersion1->setPosition(0);

        $menuButtonVersion2 = new MenuButtonVersion();
        $menuButtonVersion2->setVersion($menuVersion);
        $menuButtonVersion2->setName('Menu 2');
        $menuButtonVersion2->setType(MenuType::CLICK);
        $menuButtonVersion2->setClickKey('key_2');
        $menuButtonVersion2->setPosition(1);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->persist($menuButtonVersion1);
        self::getEntityManager()->persist($menuButtonVersion2);
        self::getEntityManager()->flush();

        $id1 = $menuButtonVersion1->getId();
        $id2 = $menuButtonVersion2->getId();

        $this->repository->updatePositions([
            $id1 => 10,
            $id2 => 5,
        ]);

        self::getEntityManager()->refresh($menuButtonVersion1);
        self::getEntityManager()->refresh($menuButtonVersion2);

        $this->assertSame(10, $menuButtonVersion1->getPosition());
        $this->assertSame(5, $menuButtonVersion2->getPosition());
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
        $menuVersion->setStatus(MenuVersionStatus::DRAFT);

        $menuButtonVersion = new MenuButtonVersion();
        $menuButtonVersion->setVersion($menuVersion);
        $menuButtonVersion->setName('Test Menu');
        $menuButtonVersion->setType(MenuType::CLICK);
        $menuButtonVersion->setClickKey('test_key');
        $menuButtonVersion->setPosition(0);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->flush();

        $this->repository->save($menuButtonVersion);

        $id = $menuButtonVersion->getId();
        $this->assertIsInt($id, 'Menu button version should have an ID after save');
        $found = $this->repository->find($id);
        $this->assertSame($menuButtonVersion, $found);
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
        $menuVersion->setStatus(MenuVersionStatus::DRAFT);

        $menuButtonVersion = new MenuButtonVersion();
        $menuButtonVersion->setVersion($menuVersion);
        $menuButtonVersion->setName('Test Menu');
        $menuButtonVersion->setType(MenuType::CLICK);
        $menuButtonVersion->setClickKey('test_key');
        $menuButtonVersion->setPosition(0);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->persist($menuButtonVersion);
        self::getEntityManager()->flush();

        $id = $menuButtonVersion->getId();
        $this->repository->remove($menuButtonVersion);

        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    public function testGetNextPositionWithParent(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setStatus(MenuVersionStatus::DRAFT);

        $parentButton = new MenuButtonVersion();
        $parentButton->setVersion($menuVersion);
        $parentButton->setName('Parent Menu');
        $parentButton->setType(MenuType::CLICK);
        $parentButton->setClickKey('parent_key');
        $parentButton->setPosition(0);
        $parentButton->setParent(null);

        $childButton = new MenuButtonVersion();
        $childButton->setVersion($menuVersion);
        $childButton->setName('Child Menu');
        $childButton->setType(MenuType::CLICK);
        $childButton->setClickKey('child_key');
        $childButton->setPosition(3);
        $childButton->setParent($parentButton);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->persist($parentButton);
        self::getEntityManager()->persist($childButton);
        self::getEntityManager()->flush();

        $nextPosition = $this->repository->getNextPosition($menuVersion, $parentButton);
        $this->assertSame(4, $nextPosition);
    }

    public function testFindOneByWithOrderByLogic(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setStatus(MenuVersionStatus::DRAFT);

        $menuButtonVersion1 = new MenuButtonVersion();
        $menuButtonVersion1->setVersion($menuVersion);
        $menuButtonVersion1->setName('Menu Z');
        $menuButtonVersion1->setType(MenuType::CLICK);
        $menuButtonVersion1->setClickKey('test_key_z');
        $menuButtonVersion1->setPosition(2);

        $menuButtonVersion2 = new MenuButtonVersion();
        $menuButtonVersion2->setVersion($menuVersion);
        $menuButtonVersion2->setName('Menu A');
        $menuButtonVersion2->setType(MenuType::CLICK);
        $menuButtonVersion2->setClickKey('test_key_a');
        $menuButtonVersion2->setPosition(1);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->persist($menuButtonVersion1);
        self::getEntityManager()->persist($menuButtonVersion2);
        self::getEntityManager()->flush();

        // Test findOneBy with ordering by name ASC
        $result = $this->repository->findOneBy(['type' => MenuType::CLICK], ['name' => 'ASC']);
        $this->assertSame($menuButtonVersion2, $result); // Menu A should come first

        // Test findOneBy with ordering by position DESC
        $result2 = $this->repository->findOneBy(['type' => MenuType::CLICK], ['position' => 'DESC']);
        $this->assertSame($menuButtonVersion1, $result2); // Position 2 should come first
    }

    public function testCountWithJoinQueries(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setStatus(MenuVersionStatus::DRAFT);

        $parentButton = new MenuButtonVersion();
        $parentButton->setVersion($menuVersion);
        $parentButton->setName('Parent Menu');
        $parentButton->setType(MenuType::CLICK);
        $parentButton->setClickKey('parent_key');
        $parentButton->setPosition(0);
        $parentButton->setParent(null);

        $childButton = new MenuButtonVersion();
        $childButton->setVersion($menuVersion);
        $childButton->setName('Child Menu');
        $childButton->setType(MenuType::CLICK);
        $childButton->setClickKey('child_key');
        $childButton->setPosition(0);
        $childButton->setParent($parentButton);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->persist($parentButton);
        self::getEntityManager()->persist($childButton);
        self::getEntityManager()->flush();

        // Test count with join query on version
        $qb = $this->repository->createQueryBuilder('mbv');
        $qb->select('COUNT(mbv.id)')
            ->innerJoin('mbv.version', 'v')
            ->where('v.id = :versionId')
            ->setParameter('versionId', $menuVersion->getId())
        ;

        $count = $qb->getQuery()->getSingleScalarResult();
        $this->assertIsNumeric($count);
        $this->assertSame(2, (int) $count);

        // Test count with join query on parent
        $qb2 = $this->repository->createQueryBuilder('mbv');
        $qb2->select('COUNT(mbv.id)')
            ->innerJoin('mbv.parent', 'p')
            ->where('p.id = :parentId')
            ->setParameter('parentId', $parentButton->getId())
        ;

        $count2 = $qb2->getQuery()->getSingleScalarResult();
        $this->assertIsNumeric($count2);
        $this->assertSame(1, (int) $count2);
    }

    public function testFindOneByAssociationVersionShouldReturnMatchingEntity(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setStatus(MenuVersionStatus::DRAFT);

        $menuButtonVersion = new MenuButtonVersion();
        $menuButtonVersion->setVersion($menuVersion);
        $menuButtonVersion->setName('Test Menu');
        $menuButtonVersion->setType(MenuType::CLICK);
        $menuButtonVersion->setClickKey('test_key');
        $menuButtonVersion->setPosition(0);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->persist($menuButtonVersion);
        self::getEntityManager()->flush();

        $result = $this->repository->findOneBy(['version' => $menuVersion]);
        $this->assertSame($menuButtonVersion, $result);
    }

    public function testCountByAssociationVersionShouldReturnCorrectNumber(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setStatus(MenuVersionStatus::DRAFT);

        $menuButtonVersion1 = new MenuButtonVersion();
        $menuButtonVersion1->setVersion($menuVersion);
        $menuButtonVersion1->setName('Test Menu 1');
        $menuButtonVersion1->setType(MenuType::CLICK);
        $menuButtonVersion1->setClickKey('test_key_1');
        $menuButtonVersion1->setPosition(0);

        $menuButtonVersion2 = new MenuButtonVersion();
        $menuButtonVersion2->setVersion($menuVersion);
        $menuButtonVersion2->setName('Test Menu 2');
        $menuButtonVersion2->setType(MenuType::CLICK);
        $menuButtonVersion2->setClickKey('test_key_2');
        $menuButtonVersion2->setPosition(1);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->persist($menuButtonVersion1);
        self::getEntityManager()->persist($menuButtonVersion2);
        self::getEntityManager()->flush();

        $count = $this->repository->count(['version' => $menuVersion]);
        $this->assertSame(2, $count);
    }

    public function testFindByNullableFields(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setStatus(MenuVersionStatus::DRAFT);

        $menuButtonVersion = new MenuButtonVersion();
        $menuButtonVersion->setVersion($menuVersion);
        $menuButtonVersion->setName('Test Menu');
        $menuButtonVersion->setType(MenuType::CLICK);
        $menuButtonVersion->setClickKey('test_key');
        $menuButtonVersion->setPosition(0);
        $menuButtonVersion->setParent(null);
        $menuButtonVersion->setUrl(null);
        $menuButtonVersion->setAppId(null);
        $menuButtonVersion->setPagePath(null);
        $menuButtonVersion->setMediaId(null);
        $menuButtonVersion->setOriginalButtonId(null);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->persist($menuButtonVersion);
        self::getEntityManager()->flush();

        // Test IS NULL queries for all nullable fields
        $resultsParent = $this->repository->findBy(['parent' => null]);
        $this->assertGreaterThanOrEqual(1, count($resultsParent));

        $resultsUrl = $this->repository->findBy(['url' => null]);
        $this->assertGreaterThanOrEqual(1, count($resultsUrl));

        $resultsAppId = $this->repository->findBy(['appId' => null]);
        $this->assertGreaterThanOrEqual(1, count($resultsAppId));

        $resultsPagePath = $this->repository->findBy(['pagePath' => null]);
        $this->assertGreaterThanOrEqual(1, count($resultsPagePath));

        $resultsMediaId = $this->repository->findBy(['mediaId' => null]);
        $this->assertGreaterThanOrEqual(1, count($resultsMediaId));

        $resultsOriginalButtonId = $this->repository->findBy(['originalButtonId' => null]);
        $this->assertGreaterThanOrEqual(1, count($resultsOriginalButtonId));
    }

    public function testCountByNullableFields(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setStatus(MenuVersionStatus::DRAFT);

        $menuButtonVersion = new MenuButtonVersion();
        $menuButtonVersion->setVersion($menuVersion);
        $menuButtonVersion->setName('Test Menu');
        $menuButtonVersion->setType(MenuType::CLICK);
        $menuButtonVersion->setClickKey('test_key');
        $menuButtonVersion->setPosition(0);
        $menuButtonVersion->setParent(null);
        $menuButtonVersion->setUrl(null);
        $menuButtonVersion->setAppId(null);
        $menuButtonVersion->setPagePath(null);
        $menuButtonVersion->setMediaId(null);
        $menuButtonVersion->setOriginalButtonId(null);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->persist($menuButtonVersion);
        self::getEntityManager()->flush();

        // Test count IS NULL queries for all nullable fields
        $countParent = $this->repository->count(['parent' => null]);
        $this->assertGreaterThanOrEqual(1, $countParent);

        $countUrl = $this->repository->count(['url' => null]);
        $this->assertGreaterThanOrEqual(1, $countUrl);

        $countAppId = $this->repository->count(['appId' => null]);
        $this->assertGreaterThanOrEqual(1, $countAppId);

        $countPagePath = $this->repository->count(['pagePath' => null]);
        $this->assertGreaterThanOrEqual(1, $countPagePath);

        $countMediaId = $this->repository->count(['mediaId' => null]);
        $this->assertGreaterThanOrEqual(1, $countMediaId);

        $countOriginalButtonId = $this->repository->count(['originalButtonId' => null]);
        $this->assertGreaterThanOrEqual(1, $countOriginalButtonId);
    }

    public function testFindWithJoinQueries(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setStatus(MenuVersionStatus::DRAFT);

        $parentButton = new MenuButtonVersion();
        $parentButton->setVersion($menuVersion);
        $parentButton->setName('Parent Menu');
        $parentButton->setType(MenuType::CLICK);
        $parentButton->setClickKey('parent_key');
        $parentButton->setPosition(0);
        $parentButton->setParent(null);

        $childButton = new MenuButtonVersion();
        $childButton->setVersion($menuVersion);
        $childButton->setName('Child Menu');
        $childButton->setType(MenuType::CLICK);
        $childButton->setClickKey('child_key');
        $childButton->setPosition(0);
        $childButton->setParent($parentButton);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->persist($parentButton);
        self::getEntityManager()->persist($childButton);
        self::getEntityManager()->flush();

        // Test join query with version
        $qb = $this->repository->createQueryBuilder('mbv');
        $qb->innerJoin('mbv.version', 'v')
            ->where('v.id = :versionId')
            ->setParameter('versionId', $menuVersion->getId())
        ;

        $results = $qb->getQuery()->getResult();
        $this->assertIsArray($results);
        $this->assertCount(2, $results);

        // Test join query with parent
        $qb2 = $this->repository->createQueryBuilder('mbv');
        $qb2->innerJoin('mbv.parent', 'p')
            ->where('p.id = :parentId')
            ->setParameter('parentId', $parentButton->getId())
        ;

        $results2 = $qb2->getQuery()->getResult();
        $this->assertIsArray($results2);
        $this->assertCount(1, $results2);
        $this->assertSame($childButton, $results2[0]);
    }

    public function testFindWithComplexRelationships(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setStatus(MenuVersionStatus::DRAFT);

        $grandParent = new MenuButtonVersion();
        $grandParent->setVersion($menuVersion);
        $grandParent->setName('Grand Parent');
        $grandParent->setType(MenuType::CLICK);
        $grandParent->setClickKey('grandparent_key');
        $grandParent->setPosition(0);
        $grandParent->setParent(null);

        $parent = new MenuButtonVersion();
        $parent->setVersion($menuVersion);
        $parent->setName('Parent');
        $parent->setType(MenuType::CLICK);
        $parent->setClickKey('parent_key');
        $parent->setPosition(0);
        $parent->setParent($grandParent);

        $child = new MenuButtonVersion();
        $child->setVersion($menuVersion);
        $child->setName('Child');
        $child->setType(MenuType::CLICK);
        $child->setClickKey('child_key');
        $child->setPosition(0);
        $child->setParent($parent);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->persist($grandParent);
        self::getEntityManager()->persist($parent);
        self::getEntityManager()->persist($child);
        self::getEntityManager()->flush();

        // Test complex join with multiple levels
        $qb = $this->repository->createQueryBuilder('mbv');
        $qb->innerJoin('mbv.parent', 'p')
            ->innerJoin('p.parent', 'gp')
            ->where('gp.id = :grandParentId')
            ->setParameter('grandParentId', $grandParent->getId())
        ;

        $results = $qb->getQuery()->getResult();
        $this->assertIsArray($results);
        $this->assertCount(1, $results);
        $this->assertSame($child, $results[0]);
    }

    public function testFindWithVersionAndAccountJoin(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('1.0.0');
        $menuVersion->setStatus(MenuVersionStatus::DRAFT);

        $menuButtonVersion = new MenuButtonVersion();
        $menuButtonVersion->setVersion($menuVersion);
        $menuButtonVersion->setName('Test Menu');
        $menuButtonVersion->setType(MenuType::CLICK);
        $menuButtonVersion->setClickKey('test_key');
        $menuButtonVersion->setPosition(0);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->persist($menuButtonVersion);
        self::getEntityManager()->flush();

        // Test join query through version to account
        $qb = $this->repository->createQueryBuilder('mbv');
        $qb->innerJoin('mbv.version', 'v')
            ->innerJoin('v.account', 'a')
            ->where('a.id = :accountId')
            ->setParameter('accountId', $account->getId())
        ;

        $results = $qb->getQuery()->getResult();
        $this->assertIsArray($results);
        $this->assertCount(1, $results);
        $this->assertSame($menuButtonVersion, $results[0]);
    }

    /**
     * @return MenuButtonVersion
     */
    protected function createNewEntity(): object
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());

        $menuVersion = new MenuVersion();
        $menuVersion->setAccount($account);
        $menuVersion->setVersion('Test Version ' . uniqid());
        $menuVersion->setStatus(MenuVersionStatus::DRAFT);

        $menuButton = new MenuButton();
        $menuButton->setAccount($account);
        $menuButton->setName('Test Button');
        $menuButton->setType(MenuType::CLICK);
        $menuButton->setClickKey('test_key');
        $menuButton->setPosition(1);
        $menuButton->setEnabled(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuVersion);
        self::getEntityManager()->persist($menuButton);
        self::getEntityManager()->flush();

        $entity = new MenuButtonVersion();
        $entity->setVersion($menuVersion);
        $entity->setName('Test MenuButtonVersion ' . uniqid());
        $entity->setType(MenuType::CLICK);
        $entity->setClickKey('test_key_' . uniqid());
        $entity->setPosition(1);
        $entity->setEnabled(true);

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<MenuButtonVersion>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
