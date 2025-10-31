<?php

namespace WechatOfficialAccountMenuBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountMenuBundle\Controller\SortMenuController;
use WechatOfficialAccountMenuBundle\Entity\MenuButton;
use WechatOfficialAccountMenuBundle\Enum\MenuType;

/**
 * @internal
 */
#[CoversClass(SortMenuController::class)]
#[RunTestsInSeparateProcesses]
final class SortMenuControllerTest extends AbstractWebTestCase
{
    public function testUnauthenticatedAccessDenied(): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(AccessDeniedException::class);
        $client->request('PATCH', '/api/wechat/menu/sort/1');
    }

    public function testOnlyPatchMethodIsAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('test@example.com', 'password123');
        $this->loginAsUser($client, 'test@example.com', 'password123');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('GET', '/api/wechat/menu/sort/1');
    }

    public function testSortMenuWithNonExistentAccount(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('test@example.com', 'password123');
        $this->loginAsUser($client, 'test@example.com', 'password123');

        $data = [
            'items' => [],
        ];

        $this->expectException(NotFoundHttpException::class);
        $client->request(
            'PATCH',
            '/api/wechat/menu/sort/999999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );
    }

    public function testSortMenuWithNonExistentAccountAndEmptyItems(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('test@example.com', 'password123');
        $this->loginAsUser($client, 'test@example.com', 'password123');

        $data = [
            'items' => [],
        ];

        $this->expectException(NotFoundHttpException::class);
        $client->request(
            'PATCH',
            '/api/wechat/menu/sort/999999', // Use non-existent account ID
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );
    }

    public function testSortMenuWithNonExistentAccountAndInvalidData(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('test2@example.com', 'password123');
        $this->loginAsUser($client, 'test2@example.com', 'password123');

        $data = [
            'items' => 'invalid', // Should be array - will be validated by DTO constraints
        ];

        $this->expectException(NotFoundHttpException::class);
        $client->request(
            'PATCH',
            '/api/wechat/menu/sort/999999', // Use non-existent account ID
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );
    }

    public function testSortMenuWithNonExistentAccountAndValidData(): void
    {
        $client = self::createClientWithDatabase();
        $this->createNormalUser('test3@example.com', 'password123');
        $this->loginAsUser($client, 'test3@example.com', 'password123');

        // Use menu buttons from fixtures (we know they exist with IDs 1 and 2 based on fixtures)
        $data = [
            'items' => [
                ['id' => '1', 'position' => 2],
                ['id' => '2', 'position' => 1],
            ],
        ];

        $this->expectException(NotFoundHttpException::class);
        $client->request(
            'PATCH',
            '/api/wechat/menu/sort/999999', // Use non-existent account ID
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->createAdminUser('admin@example.com', 'admin123');
        $this->loginAsAdmin($client, 'admin@example.com', 'admin123');

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/wechat/menu/sort/1');
    }
}
