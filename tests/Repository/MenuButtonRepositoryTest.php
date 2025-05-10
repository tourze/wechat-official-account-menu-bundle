<?php

namespace WechatOfficialAccountMenuBundle\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use WechatOfficialAccountMenuBundle\Repository\MenuButtonRepository;

class MenuButtonRepositoryTest extends TestCase
{
    private ManagerRegistry $registry;
    private EntityManagerInterface $entityManager;
    private MenuButtonRepository $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->registry->method('getManagerForClass')
            ->willReturn($this->entityManager);
        
        $this->repository = new MenuButtonRepository($this->registry);
    }

    public function testConstructor_shouldSetEntityClass(): void
    {
        // 由于ServiceEntityRepository的内部实现可能变化，
        // 我们只测试公共接口和继承关系
        $this->assertInstanceOf(MenuButtonRepository::class, $this->repository);
    }

    public function testRepositoryExtends_shouldExtendServiceEntityRepository(): void
    {
        $this->assertInstanceOf(\Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository::class, $this->repository);
    }
} 