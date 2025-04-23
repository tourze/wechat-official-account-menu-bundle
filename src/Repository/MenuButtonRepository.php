<?php

namespace WechatOfficialAccountMenuBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;

/**
 * @method MenuButton|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuButton|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuButton[]    findAll()
 * @method MenuButton[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuButtonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuButton::class);
    }
}
