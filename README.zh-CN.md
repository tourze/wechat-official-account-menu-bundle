# 微信公众号自定义菜单管理包

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue)]
(https://packagist.org/packages/tourze/wechat-official-account-menu-bundle)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![Symfony](https://img.shields.io/badge/symfony-%5E6.4-brightgreen)](https://symfony.com/)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?branch=master)](https://github.com/tourze/php-monorepo/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo)](https://codecov.io/gh/tourze/php-monorepo)

[English](README.md) | [中文](README.zh-CN.md)

微信公众号自定义菜单管理包，提供完整的菜单管理功能，包括版本管理、可视化编辑支持等。

## 目录

- [功能特性](#功能特性)
- [安装](#安装)
- [系统要求](#系统要求) 
- [依赖包](#依赖包)
- [使用方法](#使用方法)
- [高级用法](#高级用法)
- [数据模型](#数据模型)
- [事件](#事件)
- [配置](#配置)
- [安全性](#安全性)
- [限制说明](#限制说明)
- [错误处理](#错误处理)
- [开发计划](#开发计划)
- [许可证](#许可证)
- [参考文档](#参考文档)

## 功能特性

- 📋 **菜单管理**：创建、编辑、删除、排序、复制菜单
- 📦 **版本管理**：菜单版本控制、版本发布、版本回滚、版本对比
- ✅ **菜单验证**：自动验证菜单结构符合微信限制
- 🔄 **微信API集成**：自动同步菜单到微信服务器
- 🎯 **个性化菜单**：支持创建和管理个性化菜单
- 🌲 **树形结构**：支持最多两级菜单结构
- 🚀 **性能优化**：启用/禁用菜单、批量操作

## 安装

```bash
composer require tourze/wechat-official-account-menu-bundle
```

## 系统要求

- PHP 8.1 或更高版本
- Symfony 6.4 或更高版本
- Doctrine ORM
- 有效的微信公众号

## 依赖包

本包依赖以下组件：

- `symfony/framework-bundle` - Symfony 核心框架
- `doctrine/doctrine-bundle` - 数据库抽象层
- `tourze/wechat-official-account-bundle` - 微信 API 集成
- `symfony/validator` - 数据验证
- `symfony/serializer` - 对象序列化

## 使用方法

### 1. 菜单管理

#### 创建菜单
```php
use WechatOfficialAccountMenuBundle\Service\MenuService;

// 创建一级菜单
$menuData = [
    'name' => '产品介绍',
    'type' => MenuType::VIEW,
    'url' => 'https://example.com/products',
    'position' => 0,
    'enabled' => true,
];
$menuButton = $menuService->createMenuButton($account, $menuData);

// 创建二级菜单
$subMenuData = [
    'name' => '产品详情',
    'type' => MenuType::VIEW,
    'url' => 'https://example.com/product/detail',
    'parent' => $parentMenuButton,
];
$subMenuButton = $menuService->createMenuButton($account, $subMenuData);
```

#### 更新菜单
```php
$menuService->updateMenuButton($menuButton, [
    'name' => '新产品介绍',
    'url' => 'https://example.com/new-products',
]);
```

#### 菜单排序
```php
$positions = [
    'button_id_1' => 0,
    'button_id_2' => 1,
    'button_id_3' => 2,
];
$menuService->updateMenuPositions($positions);
```

### 2. 版本管理

#### 创建版本
```php
use WechatOfficialAccountMenuBundle\Service\MenuVersionService;

// 从当前菜单创建版本
$version = $menuVersionService->createVersion(
    $account,
    '2024年春节活动菜单'
);

// 从已有版本创建
$newVersion = $menuVersionService->createVersion(
    $account,
    '基于春节版本的调整',
    $existingVersion
);
```

#### 发布版本
```php
try {
    $menuVersionService->publishVersion($version);
    echo "菜单发布成功！";
} catch (MenuVersionException $e) {
    echo "发布失败：" . $e->getMessage();
}
```

#### 版本回滚
```php
$rollbackVersion = $menuVersionService->rollbackToVersion($oldVersion);
```

#### 版本对比
```php
$changes = $menuVersionService->compareVersions($version1, $version2);
// 返回结果包含：added（新增）、removed（删除）、modified（修改）
```

### 3. API 接口

所有接口都返回 JSON 格式数据。

#### 菜单版本管理接口

- `GET /admin/menu/version/{id}/menus` - 获取版本菜单
- `POST /admin/menu/version/{id}/menu` - 在版本中创建菜单
- `PUT /admin/menu/version/{id}/menu/{menuId}` - 更新版本中的菜单
- `DELETE /admin/menu/version/{id}/menu/{menuId}` - 从版本中删除菜单
- `POST /admin/menu/version/{id}/positions` - 更新菜单位置
- `POST /admin/menu/version/{id}/from-current` - 从当前菜单创建版本

### 4. 菜单类型

支持的菜单类型（MenuType）：

- `NONE` - 无动作（用于父菜单）
- `VIEW` - 跳转URL
- `CLICK` - 点击推事件
- `MINI_PROGRAM` - 小程序
- `SCAN_CODE_PUSH` - 扫码推事件
- `SCAN_CODE_WAIT_MSG` - 扫码带提示
- `PIC_SYS_PHOTO` - 系统拍照发图
- `PIC_PHOTO_ALBUM` - 拍照或者相册发图
- `PIC_WEIXIN` - 微信相册发图
- `LOCATION_SELECT` - 发送位置

### 5. 可视化编辑器集成

本包提供了完整的数据接口支持前端可视化编辑器，包括：

- 树形菜单结构数据
- 拖拽排序支持
- 实时预览
- 版本管理界面
- 菜单验证提示

## 高级用法

### 个性化菜单

基于用户属性创建条件菜单：

```php
use WechatOfficialAccountMenuBundle\Service\WechatMenuApiService;

// 为特定用户群体创建个性化菜单
$personalizedMenu = [
    'button' => $menuButtons,
    'matchrule' => [
        'tag_id' => '101',  // 用户标签ID
        'sex' => '1',       // 性别：1=男性，2=女性
        'country' => 'CN',  // 国家
        'province' => 'Guangdong',  // 省份
        'city' => 'Guangzhou',      // 城市
        'client_platform_type' => '1', // 平台：1=iOS，2=Android，3=其他
        'language' => 'zh_CN'       // 语言
    ]
];

$wechatMenuApiService->addConditionalMenu($account, $personalizedMenu);
```

### 批量操作

高效管理多个菜单：

```php
// 批量创建菜单
$menuBatchData = [
    ['name' => '菜单1', 'type' => MenuType::VIEW, 'url' => 'https://example.com/1'],
    ['name' => '菜单2', 'type' => MenuType::VIEW, 'url' => 'https://example.com/2'],
    ['name' => '菜单3', 'type' => MenuType::CLICK, 'key' => 'MENU_3_KEY'],
];

foreach ($menuBatchData as $index => $data) {
    $data['position'] = $index;
    $menuService->createMenuButton($account, $data);
}

// 批量启用/禁用菜单
$menuService->batchUpdateStatus($menuIds, true);  // 启用所有
$menuService->batchUpdateStatus($menuIds, false); // 禁用所有
```

### 错误恢复

优雅处理失败操作：

```php
use WechatOfficialAccountMenuBundle\Exception\WechatApiException;

try {
    $menuVersionService->publishVersion($version);
} catch (WechatApiException $e) {
    // 记录错误
    $logger->error('菜单发布失败', [
        'error_code' => $e->getErrorCode(),
        'error_msg' => $e->getMessage(),
        'version_id' => $version->getId()
    ]);
    
    // 尝试恢复
    if ($e->getErrorCode() === 48001) { // API未授权
        // 刷新访问令牌并重试
        $accessTokenService->refreshToken($account);
        $menuVersionService->publishVersion($version);
    }
}
```

## 数据模型

### MenuButton（菜单按钮）
- 支持树形结构（最多两级）
- 包含排序和启用/禁用状态
- 自动验证菜单结构

### MenuVersion（菜单版本）
- 版本号自动生成
- 状态：草稿、已发布、已归档
- 保存菜单快照
- 记录发布历史

### MenuButtonVersion（版本化的菜单按钮）
- 关联到特定版本
- 保持原始按钮ID用于对比

## 事件

- `MenuVersionCreatedEvent` - 版本创建时触发
- `MenuVersionPublishedEvent` - 版本发布时触发

## 配置

在 `config/packages/wechat_official_account_menu.yaml` 中配置：

```yaml
# 目前无需特殊配置，包会自动注册所有服务
```

## 安全性

### 访问控制
- 在进行菜单操作前始终验证用户权限
- 为菜单管理实施基于角色的访问控制
- 对所有菜单修改表单使用 CSRF 保护

### 数据验证
- 所有输入数据均使用 Symfony Validator 进行验证
- 菜单结构按微信 API 要求进行验证
- URL 验证防止恶意重定向

### 微信 API 安全
- 安全存储微信访问令牌
- 为 API 调用实施速率限制
- 所有微信 API 通信使用 HTTPS

### 推荐做法
```php
// 始终验证账号所有权
if ($menu->getAccount()->getId() !== $currentUserAccountId) {
    throw new AccessDeniedException('权限不足');
}

// 设置前验证 URL
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    throw new InvalidArgumentException('无效的 URL 格式');
}
```

## 限制说明

根据微信官方文档的限制：

- 一级菜单最多3个
- 每个一级菜单最多5个二级菜单
- 菜单名称最多60个字符
- Key值最多128个字符
- URL最多1024个字符

## 错误处理

```php
use WechatOfficialAccountMenuBundle\Exception\MenuValidationException;
use WechatOfficialAccountMenuBundle\Exception\MenuVersionException;
use WechatOfficialAccountMenuBundle\Exception\WechatApiException;

try {
    // 菜单操作
} catch (MenuValidationException $e) {
    // 菜单验证失败
} catch (MenuVersionException $e) {
    // 版本操作失败
} catch (WechatApiException $e) {
    // 微信API调用失败
    $errorCode = $e->getErrorCode();
}
```

## 开发计划

- [ ] 支持媒体素材菜单
- [ ] 支持图文消息菜单
- [ ] 批量导入/导出功能
- [ ] 菜单使用统计
- [ ] 更多的个性化菜单条件

## 贡献指南

我们欢迎为这个项目做出贡献！以下是参与方法：

### 问题报告
- 使用 [GitHub 问题跟踪器](https://github.com/tourze/php-monorepo/issues) 报告错误
- 包含尽可能多的详细信息（PHP 版本、Symfony 版本、错误信息）
- 提供能重现问题的最小代码示例

### 代码贡献
- Fork 仓库并创建功能分支
- 遵循现有的代码风格和约定
- 为新功能编写测试
- 提交 PR 前确保所有测试通过
- 如需要请更新文档

### 代码标准
- 遵循 PSR-12 编码标准
- 适当使用 PHP 8.1+ 特性
- 尽可能保持向后兼容性
- 为所有方法参数和返回值添加类型声明

### 测试
- 所有新功能必须包含单元测试
- 服务和存储库需要集成测试
- 保持测试覆盖率在 80% 以上

## 许可证

本项目基于 MIT 许可证发布 - 查看 [LICENSE](LICENSE) 文件了解详情。

## 参考文档

- [微信官方文档 - 自定义菜单创建接口]
  (https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Creating_Custom-Defined_Menu.html)
- [微信官方文档 - 个性化菜单接口]
  (https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Personalized_menu_interface.html)
- [微信官方文档 - 获取自定义菜单配置]
  (https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Getting_Custom_Menu_Configurations.html)
