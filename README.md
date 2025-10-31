# WeChat Official Account Menu Bundle

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue)]
(https://packagist.org/packages/tourze/wechat-official-account-menu-bundle)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![Symfony](https://img.shields.io/badge/symfony-%5E6.4-brightgreen)](https://symfony.com/)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?branch=master)](https://github.com/tourze/php-monorepo/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo)](https://codecov.io/gh/tourze/php-monorepo)

[English](README.md) | [中文](README.zh-CN.md)

A comprehensive WeChat Official Account custom menu management bundle for Symfony,
providing complete menu management functionality with version control and visual editing support.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Requirements](#requirements) 
- [Dependencies](#dependencies)
- [Usage](#usage)
- [Advanced Usage](#advanced-usage)
- [Data Models](#data-models)
- [Events](#events)
- [Configuration](#configuration)
- [Security](#security)
- [Limitations](#limitations)
- [Error Handling](#error-handling)
- [Roadmap](#roadmap)
- [License](#license)
- [References](#references)

## Features

- 📋 **Menu Management**: Create, edit, delete, sort, and copy menus
- 📦 **Version Control**: Menu versioning, publishing, rollback, and comparison
- ✅ **Menu Validation**: Automatic validation against WeChat restrictions
- 🔄 **WeChat API Integration**: Automatic menu synchronization to WeChat servers
- 🎯 **Personalized Menus**: Support for creating and managing personalized menus
- 🌲 **Tree Structure**: Support for up to two-level menu hierarchy
- 🚀 **Performance Optimization**: Enable/disable menus, batch operations

## Installation

```bash
composer require tourze/wechat-official-account-menu-bundle
```

## Requirements

- PHP 8.1 or higher
- Symfony 6.4 or higher
- Doctrine ORM
- Valid WeChat Official Account

## Dependencies

This bundle requires the following packages:

- `symfony/framework-bundle` - Core Symfony framework
- `doctrine/doctrine-bundle` - Database abstraction layer
- `tourze/wechat-official-account-bundle` - WeChat API integration
- `symfony/validator` - Data validation
- `symfony/serializer` - Object serialization

## Usage

### 1. Menu Management

#### Creating Menus
```php
use WechatOfficialAccountMenuBundle\Service\MenuService;

// Create top-level menu
$menuData = [
    'name' => 'Products',
    'type' => MenuType::VIEW,
    'url' => 'https://example.com/products',
    'position' => 0,
    'enabled' => true,
];
$menuButton = $menuService->createMenuButton($account, $menuData);

// Create sub-menu
$subMenuData = [
    'name' => 'Product Details',
    'type' => MenuType::VIEW,
    'url' => 'https://example.com/product/detail',
    'parent' => $parentMenuButton,
];
$subMenuButton = $menuService->createMenuButton($account, $subMenuData);
```

#### Updating Menus
```php
$menuService->updateMenuButton($menuButton, [
    'name' => 'New Products',
    'url' => 'https://example.com/new-products',
]);
```

#### Menu Sorting
```php
$positions = [
    'button_id_1' => 0,
    'button_id_2' => 1,
    'button_id_3' => 2,
];
$menuService->updateMenuPositions($positions);
```

### 2. Version Management

#### Creating Versions
```php
use WechatOfficialAccountMenuBundle\Service\MenuVersionService;

// Create version from current menu
$version = $menuVersionService->createVersion(
    $account,
    '2024 Spring Festival Menu'
);

// Create from existing version
$newVersion = $menuVersionService->createVersion(
    $account,
    'Adjusted from Spring Festival version',
    $existingVersion
);
```

#### Publishing Versions
```php
try {
    $menuVersionService->publishVersion($version);
    echo "Menu published successfully!";
} catch (MenuVersionException $e) {
    echo "Publishing failed: " . $e->getMessage();
}
```

#### Version Rollback
```php
$rollbackVersion = $menuVersionService->rollbackToVersion($oldVersion);
```

#### Version Comparison
```php
$changes = $menuVersionService->compareVersions($version1, $version2);
// Returns: added, removed, modified
```

### 3. API Endpoints

All endpoints return JSON format data.

#### Menu Version API

- `GET /admin/menu/version/{id}/menus` - Get version menus
- `POST /admin/menu/version/{id}/menu` - Create menu in version
- `PUT /admin/menu/version/{id}/menu/{menuId}` - Update menu in version
- `DELETE /admin/menu/version/{id}/menu/{menuId}` - Delete menu from version
- `POST /admin/menu/version/{id}/positions` - Update menu positions
- `POST /admin/menu/version/{id}/from-current` - Create version from current menu

### 4. Menu Types

Supported menu types (MenuType):

- `NONE` - No action (for parent menus)
- `VIEW` - Jump to URL
- `CLICK` - Click push event
- `MINI_PROGRAM` - Mini Program
- `SCAN_CODE_PUSH` - Scan code push event
- `SCAN_CODE_WAIT_MSG` - Scan code with prompt
- `PIC_SYS_PHOTO` - System photo
- `PIC_PHOTO_ALBUM` - Photo album
- `PIC_WEIXIN` - WeChat album
- `LOCATION_SELECT` - Send location

### 5. Visual Editor Integration

This package provides complete data interface support for frontend visual editors, including:

- Tree menu structure data
- Drag-and-drop sorting support
- Real-time preview
- Version management interface
- Menu validation prompts

## Advanced Usage

### Personalized Menus

Create conditional menus based on user attributes:

```php
use WechatOfficialAccountMenuBundle\Service\WechatMenuApiService;

// Create personalized menu for specific user groups
$personalizedMenu = [
    'button' => $menuButtons,
    'matchrule' => [
        'tag_id' => '101',  // User tag ID
        'sex' => '1',       // Gender: 1=Male, 2=Female
        'country' => 'CN',  // Country
        'province' => 'Guangdong',  // Province
        'city' => 'Guangzhou',      // City
        'client_platform_type' => '1', // Platform: 1=iOS, 2=Android, 3=Others
        'language' => 'zh_CN'       // Language
    ]
];

$wechatMenuApiService->addConditionalMenu($account, $personalizedMenu);
```

### Batch Operations

Efficiently manage multiple menus:

```php
// Batch create menus
$menuBatchData = [
    ['name' => 'Menu 1', 'type' => MenuType::VIEW, 'url' => 'https://example.com/1'],
    ['name' => 'Menu 2', 'type' => MenuType::VIEW, 'url' => 'https://example.com/2'],
    ['name' => 'Menu 3', 'type' => MenuType::CLICK, 'key' => 'MENU_3_KEY'],
];

foreach ($menuBatchData as $index => $data) {
    $data['position'] = $index;
    $menuService->createMenuButton($account, $data);
}

// Batch enable/disable menus
$menuService->batchUpdateStatus($menuIds, true); // Enable all
$menuService->batchUpdateStatus($menuIds, false); // Disable all
```

### Menu Analytics and Monitoring

Track menu performance:

```php
// Get menu click statistics (requires custom implementation)
$stats = $menuService->getMenuClickStats($account, $startDate, $endDate);

// Monitor menu synchronization status
$syncStatus = $wechatMenuApiService->getCurrentMenuInfo($account);
if ($syncStatus['is_menu_open'] === 1) {
    echo "Menu is enabled on WeChat";
}
```

### Error Recovery

Handle failed operations gracefully:

```php
use WechatOfficialAccountMenuBundle\Exception\WechatApiException;

try {
    $menuVersionService->publishVersion($version);
} catch (WechatApiException $e) {
    // Log the error
    $logger->error('Menu publish failed', [
        'error_code' => $e->getErrorCode(),
        'error_msg' => $e->getMessage(),
        'version_id' => $version->getId()
    ]);
    
    // Attempt recovery
    if ($e->getErrorCode() === 48001) { // API unauthorized
        // Refresh access token and retry
        $accessTokenService->refreshToken($account);
        $menuVersionService->publishVersion($version);
    }
}
```

## Data Models

### MenuButton (Menu Button)
- Supports tree structure (up to two levels)
- Includes sorting and enable/disable status
- Automatic menu structure validation

### MenuVersion (Menu Version)
- Auto-generated version numbers
- Status: draft, published, archived
- Saves menu snapshots
- Records publishing history

### MenuButtonVersion (Versioned Menu Button)
- Associated with specific versions
- Maintains original button ID for comparison

## Events

- `MenuVersionCreatedEvent` - Triggered when version is created
- `MenuVersionPublishedEvent` - Triggered when version is published

## Configuration

Configure in `config/packages/wechat_official_account_menu.yaml`:

```yaml
# No special configuration needed currently, all services are auto-registered
```

## Security

### Access Control
- Always validate user permissions before menu operations
- Implement proper role-based access control for menu management
- Use CSRF protection for all menu modification forms

### Data Validation
- All input data is validated using Symfony Validator
- Menu structures are validated against WeChat API requirements
- URL validation prevents malicious redirects

### WeChat API Security
- Store WeChat access tokens securely
- Implement rate limiting for API calls
- Use HTTPS for all WeChat API communications

### Recommended Practices
```php
// Always validate account ownership
if ($menu->getAccount()->getId() !== $currentUserAccountId) {
    throw new AccessDeniedException('Insufficient permissions');
}

// Validate URLs before setting
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    throw new InvalidArgumentException('Invalid URL format');
}
```

## Limitations

According to WeChat official documentation:

- Maximum 3 top-level menus
- Maximum 5 sub-menus per top-level menu
- Menu name maximum 60 characters
- Key value maximum 128 characters
- URL maximum 1024 characters

## Error Handling

```php
use WechatOfficialAccountMenuBundle\Exception\MenuValidationException;
use WechatOfficialAccountMenuBundle\Exception\MenuVersionException;
use WechatOfficialAccountMenuBundle\Exception\WechatApiException;

try {
    // Menu operations
} catch (MenuValidationException $e) {
    // Menu validation failed
} catch (MenuVersionException $e) {
    // Version operation failed
} catch (WechatApiException $e) {
    // WeChat API call failed
    $errorCode = $e->getErrorCode();
}
```

## Roadmap

- [ ] Support for media material menus
- [ ] Support for rich media message menus
- [ ] Batch import/export functionality
- [ ] Menu usage statistics
- [ ] More personalized menu conditions

## Contributing

We welcome contributions to this project! Here's how you can help:

### Reporting Issues
- Use the [GitHub issue tracker](https://github.com/tourze/php-monorepo/issues) to report bugs
- Include as much detail as possible (PHP version, Symfony version, error messages)
- Provide a minimal code example that reproduces the issue

### Contributing Code
- Fork the repository and create a feature branch
- Follow the existing code style and conventions
- Write tests for new functionality
- Ensure all tests pass before submitting a PR
- Update documentation if needed

### Code Standards
- Follow PSR-12 coding standards
- Use PHP 8.1+ features where appropriate
- Maintain backward compatibility when possible
- Add type declarations for all method parameters and return values

### Testing
- All new features must include unit tests
- Integration tests are required for services and repositories
- Maintain test coverage above 80%

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## References

- [WeChat Official Documentation - Custom Menu Creation]
  (https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Creating_Custom-Defined_Menu.html)
- [WeChat Official Documentation - Personalized Menu Interface]
  (https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Personalized_menu_interface.html)
- [WeChat Official Documentation - Get Custom Menu Configuration]
  (https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Getting_Custom_Menu_Configurations.html)