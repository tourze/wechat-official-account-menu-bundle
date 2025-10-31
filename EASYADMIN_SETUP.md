# WeChat Official Account Menu Bundle - EasyAdmin Integration

## 解决 "app_login" 路由错误

如果在运行 EasyAdmin 时遇到 `Unable to generate a URL for the named route "app_login"` 错误，请按以下步骤解决：

### 方案一：配置登录路由（推荐）

1. 在主应用中创建 SecurityController：

```php
<?php
// src/Controller/SecurityController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
```

2. 创建登录模板 `templates/security/login.html.twig`（参见主应用模板示例）

### 方案二：禁用认证（开发环境）

如果不需要认证，可以修改 DashboardController：

```php
public function configureDashboard(): Dashboard
{
    return Dashboard::new()
        ->setTitle('微信公众号菜单管理')
        ->setFaviconPath('favicon.ico')
        ->generateRelativeUrls()
        ->disableUrlSignatures()  // 禁用URL签名
    ;
}
```

### 方案三：自定义登录路由

```php
public function configureDashboard(): Dashboard
{
    return Dashboard::new()
        ->setTitle('微信公众号菜单管理')
        ->setFaviconPath('favicon.ico')
        ->generateRelativeUrls()
        ->setLoginUrl('/custom-login')    // 自定义登录路由
        ->setLogoutUrl('/custom-logout')  // 自定义退出路由
    ;
}
```

## Bundle 注册

确保在 `config/bundles.php` 或 `config/bundles-local.php` 中注册此 bundle：

```php
<?php
return [
    // ... 其他 bundles
    WechatOfficialAccountMenuBundle\WechatOfficialAccountMenuBundle::class => ['all' => true],
];
```

## 访问管理界面

Bundle 注册成功后，可以通过以下路由访问：

- 主仪表板：`/admin/wechat-menu`
- 菜单按钮管理：通过仪表板菜单导航

## 疑难解答

1. **路由未找到**：确保 bundle 已正确注册并清空缓存
2. **权限错误**：检查安全配置和防火墙设置
3. **模板错误**：确保 Twig 已正确配置

如需更多帮助，请查看完整的 [README.md](README.md) 文档。