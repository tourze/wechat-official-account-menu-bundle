<?php

namespace WechatOfficialAccountMenuBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Enum\MenuType;
use WechatOfficialAccountMenuBundle\Repository\MenuButtonRepository;

/**
 * @internal
 */
#[CoversClass(MenuButtonRepository::class)]
#[RunTestsInSeparateProcesses]
final class MenuButtonRepositoryTest extends AbstractRepositoryTestCase
{
    private MenuButtonRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(MenuButtonRepository::class);
    }

    public function testFindOneByWithOrderBy(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());

        $menuButton1 = new MenuButton();
        $menuButton1->setAccount($account);
        $menuButton1->setName('Menu B');
        $menuButton1->setType(MenuType::CLICK);
        $menuButton1->setClickKey('test_key_b');
        $menuButton1->setPosition(1);
        $menuButton1->setEnabled(true);

        $menuButton2 = new MenuButton();
        $menuButton2->setAccount($account);
        $menuButton2->setName('Menu A');
        $menuButton2->setType(MenuType::CLICK);
        $menuButton2->setClickKey('test_key_a');
        $menuButton2->setPosition(0);
        $menuButton2->setEnabled(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuButton1);
        self::getEntityManager()->persist($menuButton2);
        self::getEntityManager()->flush();

        $result = $this->repository->findOneBy(['enabled' => true], ['name' => 'ASC']);
        $this->assertSame($menuButton2, $result); // Menu A comes first
    }

    public function testFindRootMenusByAccount(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());

        $rootMenu = new MenuButton();
        $rootMenu->setAccount($account);
        $rootMenu->setName('Root Menu');
        $rootMenu->setType(MenuType::CLICK);
        $rootMenu->setClickKey('root_key');
        $rootMenu->setPosition(0);
        $rootMenu->setEnabled(true);
        $rootMenu->setParent(null);

        $childMenu = new MenuButton();
        $childMenu->setAccount($account);
        $childMenu->setName('Child Menu');
        $childMenu->setType(MenuType::CLICK);
        $childMenu->setClickKey('child_key');
        $childMenu->setPosition(0);
        $childMenu->setEnabled(true);
        $childMenu->setParent($rootMenu);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($rootMenu);
        self::getEntityManager()->persist($childMenu);
        self::getEntityManager()->flush();

        $results = $this->repository->findRootMenusByAccount($account);
        $this->assertCount(1, $results);
        $this->assertSame($rootMenu, $results[0]);
    }

    public function testFindAllByAccount(): void
    {
        $account1 = new Account();
        $account1->setName('Test Account 1');
        $account1->setAppId('test_app_id_1');
        $account1->setAppSecret('test_app_secret_1');

        $account2 = new Account();
        $account2->setName('Test Account 2');
        $account2->setAppId('test_app_id_2');
        $account2->setAppSecret('test_app_secret_2');

        $menuButton1 = new MenuButton();
        $menuButton1->setAccount($account1);
        $menuButton1->setName('Menu 1');
        $menuButton1->setType(MenuType::CLICK);
        $menuButton1->setClickKey('key_1');
        $menuButton1->setPosition(0);
        $menuButton1->setEnabled(true);

        $menuButton2 = new MenuButton();
        $menuButton2->setAccount($account2);
        $menuButton2->setName('Menu 2');
        $menuButton2->setType(MenuType::CLICK);
        $menuButton2->setClickKey('key_2');
        $menuButton2->setPosition(0);
        $menuButton2->setEnabled(true);

        self::getEntityManager()->persist($account1);
        self::getEntityManager()->persist($account2);
        self::getEntityManager()->persist($menuButton1);
        self::getEntityManager()->persist($menuButton2);
        self::getEntityManager()->flush();

        $results = $this->repository->findAllByAccount($account1);
        $this->assertCount(1, $results);
        $this->assertSame($menuButton1, $results[0]);
    }

    public function testFindAccountsWithMenus(): void
    {
        $account1 = new Account();
        $account1->setName('Test Account 1');
        $account1->setAppId('test_app_id_1');
        $account1->setAppSecret('test_app_secret_1');

        $account2 = new Account();
        $account2->setName('Test Account 2');
        $account2->setAppId('test_app_id_2');
        $account2->setAppSecret('test_app_secret_2');

        $menuButton = new MenuButton();
        $menuButton->setAccount($account1);
        $menuButton->setName('Menu');
        $menuButton->setType(MenuType::CLICK);
        $menuButton->setClickKey('key');
        $menuButton->setPosition(0);
        $menuButton->setEnabled(true);

        self::getEntityManager()->persist($account1);
        self::getEntityManager()->persist($account2);
        self::getEntityManager()->persist($menuButton);
        self::getEntityManager()->flush();

        $results = $this->repository->findAccountsWithMenus();
        $this->assertGreaterThanOrEqual(1, count($results));
        $this->assertContains($account1, $results);
    }

    public function testSave(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuButton = new MenuButton();
        $menuButton->setAccount($account);
        $menuButton->setName('Test Menu');
        $menuButton->setType(MenuType::CLICK);
        $menuButton->setClickKey('test_key');
        $menuButton->setPosition(0);
        $menuButton->setEnabled(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $this->repository->save($menuButton);

        $id = $menuButton->getId();
        $this->assertIsInt($id, 'Menu button should have an ID after save');
        $found = $this->repository->find($id);
        $this->assertSame($menuButton, $found);
    }

    public function testRemove(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuButton = new MenuButton();
        $menuButton->setAccount($account);
        $menuButton->setName('Test Menu');
        $menuButton->setType(MenuType::CLICK);
        $menuButton->setClickKey('test_key');
        $menuButton->setPosition(0);
        $menuButton->setEnabled(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuButton);
        self::getEntityManager()->flush();

        $id = $menuButton->getId();
        $this->repository->remove($menuButton);

        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    public function testUpdatePositions(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());

        $menuButton1 = new MenuButton();
        $menuButton1->setAccount($account);
        $menuButton1->setName('Menu 1');
        $menuButton1->setType(MenuType::CLICK);
        $menuButton1->setClickKey('key_1');
        $menuButton1->setPosition(0);
        $menuButton1->setEnabled(true);

        $menuButton2 = new MenuButton();
        $menuButton2->setAccount($account);
        $menuButton2->setName('Menu 2');
        $menuButton2->setType(MenuType::CLICK);
        $menuButton2->setClickKey('key_2');
        $menuButton2->setPosition(1);
        $menuButton2->setEnabled(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuButton1);
        self::getEntityManager()->persist($menuButton2);
        self::getEntityManager()->flush();

        $id1 = $menuButton1->getId();
        $id2 = $menuButton2->getId();

        $this->repository->updatePositions([
            $id1 => 10,
            $id2 => 5,
        ]);

        self::getEntityManager()->refresh($menuButton1);
        self::getEntityManager()->refresh($menuButton2);

        $this->assertSame(10, $menuButton1->getPosition());
        $this->assertSame(5, $menuButton2->getPosition());
    }

    public function testGetNextPosition(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());

        $menuButton = new MenuButton();
        $menuButton->setAccount($account);
        $menuButton->setName('Menu');
        $menuButton->setType(MenuType::CLICK);
        $menuButton->setClickKey('key');
        $menuButton->setPosition(5);
        $menuButton->setEnabled(true);
        $menuButton->setParent(null);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuButton);
        self::getEntityManager()->flush();

        $nextPosition = $this->repository->getNextPosition($account);
        $this->assertSame(6, $nextPosition);
    }

    public function testGetNextPositionWithParent(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());

        $parentButton = new MenuButton();
        $parentButton->setAccount($account);
        $parentButton->setName('Parent Menu');
        $parentButton->setType(MenuType::CLICK);
        $parentButton->setClickKey('parent_key');
        $parentButton->setPosition(0);
        $parentButton->setEnabled(true);
        $parentButton->setParent(null);

        $childButton = new MenuButton();
        $childButton->setAccount($account);
        $childButton->setName('Child Menu');
        $childButton->setType(MenuType::CLICK);
        $childButton->setClickKey('child_key');
        $childButton->setPosition(3);
        $childButton->setEnabled(true);
        $childButton->setParent($parentButton);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($parentButton);
        self::getEntityManager()->persist($childButton);
        self::getEntityManager()->flush();

        $nextPosition = $this->repository->getNextPosition($account, $parentButton);
        $this->assertSame(4, $nextPosition);
    }

    public function testFindByNullableFields(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());

        $menuButton = new MenuButton();
        $menuButton->setAccount($account);
        $menuButton->setName('Test Menu');
        $menuButton->setType(MenuType::CLICK);
        $menuButton->setClickKey('test_key');
        $menuButton->setPosition(0);
        $menuButton->setEnabled(true);
        $menuButton->setParent(null);
        $menuButton->setUrl(null);
        $menuButton->setAppId(null);
        $menuButton->setPagePath(null);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuButton);
        self::getEntityManager()->flush();

        // Test IS NULL queries
        $resultsParent = $this->repository->findBy(['parent' => null]);
        $this->assertGreaterThanOrEqual(1, count($resultsParent));

        $resultsUrl = $this->repository->findBy(['url' => null]);
        $this->assertGreaterThanOrEqual(1, count($resultsUrl));

        $resultsAppId = $this->repository->findBy(['appId' => null]);
        $this->assertGreaterThanOrEqual(1, count($resultsAppId));

        $resultsPagePath = $this->repository->findBy(['pagePath' => null]);
        $this->assertGreaterThanOrEqual(1, count($resultsPagePath));
    }

    public function testCountByNullableFields(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());

        $menuButton = new MenuButton();
        $menuButton->setAccount($account);
        $menuButton->setName('Test Menu');
        $menuButton->setType(MenuType::CLICK);
        $menuButton->setClickKey('test_key');
        $menuButton->setPosition(0);
        $menuButton->setEnabled(true);
        $menuButton->setParent(null);
        $menuButton->setUrl(null);
        $menuButton->setAppId(null);
        $menuButton->setPagePath(null);
        $menuButton->setMediaId(null);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuButton);
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
    }

    public function testFindWithJoinQueries(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());

        $parentButton = new MenuButton();
        $parentButton->setAccount($account);
        $parentButton->setName('Parent Menu');
        $parentButton->setType(MenuType::CLICK);
        $parentButton->setClickKey('parent_key');
        $parentButton->setPosition(0);
        $parentButton->setEnabled(true);
        $parentButton->setParent(null);

        $childButton = new MenuButton();
        $childButton->setAccount($account);
        $childButton->setName('Child Menu');
        $childButton->setType(MenuType::CLICK);
        $childButton->setClickKey('child_key');
        $childButton->setPosition(0);
        $childButton->setEnabled(true);
        $childButton->setParent($parentButton);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($parentButton);
        self::getEntityManager()->persist($childButton);
        self::getEntityManager()->flush();

        // Test join query with account
        $qb = $this->repository->createQueryBuilder('m');
        $qb->innerJoin('m.account', 'a')
            ->where('a.id = :accountId')
            ->setParameter('accountId', $account->getId())
        ;

        $results = $qb->getQuery()->getResult();
        $this->assertIsArray($results);
        $this->assertCount(2, $results);

        // Test join query with parent
        $qb2 = $this->repository->createQueryBuilder('m');
        $qb2->innerJoin('m.parent', 'p')
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

        $grandParent = new MenuButton();
        $grandParent->setAccount($account);
        $grandParent->setName('Grand Parent');
        $grandParent->setType(MenuType::CLICK);
        $grandParent->setClickKey('grandparent_key');
        $grandParent->setPosition(0);
        $grandParent->setEnabled(true);
        $grandParent->setParent(null);

        $parent = new MenuButton();
        $parent->setAccount($account);
        $parent->setName('Parent');
        $parent->setType(MenuType::CLICK);
        $parent->setClickKey('parent_key');
        $parent->setPosition(0);
        $parent->setEnabled(true);
        $parent->setParent($grandParent);

        $child = new MenuButton();
        $child->setAccount($account);
        $child->setName('Child');
        $child->setType(MenuType::CLICK);
        $child->setClickKey('child_key');
        $child->setPosition(0);
        $child->setEnabled(true);
        $child->setParent($parent);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($grandParent);
        self::getEntityManager()->persist($parent);
        self::getEntityManager()->persist($child);
        self::getEntityManager()->flush();

        // Test complex join with multiple levels
        $qb = $this->repository->createQueryBuilder('m');
        $qb->innerJoin('m.parent', 'p')
            ->innerJoin('p.parent', 'gp')
            ->where('gp.id = :grandParentId')
            ->setParameter('grandParentId', $grandParent->getId())
        ;

        $results = $qb->getQuery()->getResult();
        $this->assertIsArray($results);
        $this->assertCount(1, $results);
        $this->assertSame($child, $results[0]);
    }

    public function testCountWithJoinQueries(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());

        $parentButton = new MenuButton();
        $parentButton->setAccount($account);
        $parentButton->setName('Parent Menu');
        $parentButton->setType(MenuType::CLICK);
        $parentButton->setClickKey('parent_key');
        $parentButton->setPosition(0);
        $parentButton->setEnabled(true);
        $parentButton->setParent(null);

        $childButton = new MenuButton();
        $childButton->setAccount($account);
        $childButton->setName('Child Menu');
        $childButton->setType(MenuType::CLICK);
        $childButton->setClickKey('child_key');
        $childButton->setPosition(0);
        $childButton->setEnabled(true);
        $childButton->setParent($parentButton);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($parentButton);
        self::getEntityManager()->persist($childButton);
        self::getEntityManager()->flush();

        // Test count with join query on account
        $qb = $this->repository->createQueryBuilder('m');
        $qb->select('COUNT(m.id)')
            ->innerJoin('m.account', 'a')
            ->where('a.id = :accountId')
            ->setParameter('accountId', $account->getId())
        ;

        $count = $qb->getQuery()->getSingleScalarResult();
        $this->assertIsNumeric($count);
        $this->assertSame(2, (int) $count);

        // Test count with join query on parent
        $qb2 = $this->repository->createQueryBuilder('m');
        $qb2->select('COUNT(m.id)')
            ->innerJoin('m.parent', 'p')
            ->where('p.id = :parentId')
            ->setParameter('parentId', $parentButton->getId())
        ;

        $count2 = $qb2->getQuery()->getSingleScalarResult();
        $this->assertIsNumeric($count2);
        $this->assertSame(1, (int) $count2);
    }

    public function testFindOneByAssociationAccountShouldReturnMatchingEntity(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());
        $menuButton = new MenuButton();
        $menuButton->setAccount($account);
        $menuButton->setName('Test Menu');
        $menuButton->setType(MenuType::CLICK);
        $menuButton->setClickKey('test_key');
        $menuButton->setPosition(0);
        $menuButton->setEnabled(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuButton);
        self::getEntityManager()->flush();

        $result = $this->repository->findOneBy(['account' => $account]);
        $this->assertSame($menuButton, $result);
    }

    public function testCountByAssociationAccountShouldReturnCorrectNumber(): void
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());

        $menuButton1 = new MenuButton();
        $menuButton1->setAccount($account);
        $menuButton1->setName('Test Menu 1');
        $menuButton1->setType(MenuType::CLICK);
        $menuButton1->setClickKey('test_key_1');
        $menuButton1->setPosition(0);
        $menuButton1->setEnabled(true);

        $menuButton2 = new MenuButton();
        $menuButton2->setAccount($account);
        $menuButton2->setName('Test Menu 2');
        $menuButton2->setType(MenuType::CLICK);
        $menuButton2->setClickKey('test_key_2');
        $menuButton2->setPosition(1);
        $menuButton2->setEnabled(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($menuButton1);
        self::getEntityManager()->persist($menuButton2);
        self::getEntityManager()->flush();

        $count = $this->repository->count(['account' => $account]);
        $this->assertSame(2, $count);
    }

    /**
     * @return MenuButton
     */
    protected function createNewEntity(): object
    {
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret_' . uniqid());

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $entity = new MenuButton();
        $entity->setAccount($account);
        $entity->setName('Test MenuButton ' . uniqid());
        $entity->setType(MenuType::CLICK);
        $entity->setClickKey('test_key_' . uniqid());
        $entity->setPosition(1);
        $entity->setEnabled(true);

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<MenuButton>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
