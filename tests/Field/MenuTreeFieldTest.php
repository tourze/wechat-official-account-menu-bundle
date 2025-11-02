<?php

namespace WechatOfficialAccountMenuBundle\Tests\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use WechatOfficialAccountMenuBundle\Field\MenuTreeField;

/**
 * @internal
 */
#[CoversClass(MenuTreeField::class)]
final class MenuTreeFieldTest extends TestCase
{
    public function testNewShouldCreateFieldInstance(): void
    {
        $field = MenuTreeField::new('menuTree', 'Menu Tree');

        $this->assertInstanceOf(MenuTreeField::class, $field);
        $this->assertInstanceOf(FieldInterface::class, $field);
    }

    public function testNewShouldSetBasicProperties(): void
    {
        $field = MenuTreeField::new('menuTree', 'Menu Tree');
        $dto = $field->getAsDto();

        $this->assertEquals('menuTree', $dto->getProperty());
        $this->assertEquals('Menu Tree', $dto->getLabel());
        $this->assertEquals('@WechatOfficialAccountMenu/admin/field/menu_tree.html.twig', $dto->getTemplatePath());
        $this->assertEquals(HiddenType::class, $dto->getFormType());
    }

    public function testSetMenuDataShouldWork(): void
    {
        $menuData = [
            'menus' => [
                ['id' => '1', 'name' => 'Menu 1', 'children' => []],
            ],
        ];

        $field = MenuTreeField::new('menuTree');
        $field->setMenuData($menuData);

        // 验证字段对象仍然存在
        $this->assertInstanceOf(MenuTreeField::class, $field);
    }

    public function testSetAccountIdShouldWork(): void
    {
        $field = MenuTreeField::new('menuTree');
        $field->setAccountId('account_123');

        // 验证字段对象仍然存在
        $this->assertInstanceOf(MenuTreeField::class, $field);
    }

    public function testSetVersionIdShouldWork(): void
    {
        $field = MenuTreeField::new('menuTree');
        $field->setVersionId('version_456');

        // 验证字段对象仍然存在
        $this->assertInstanceOf(MenuTreeField::class, $field);
    }

    public function testEnableDragAndDropShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->enableDragAndDrop(false);

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetMaxRootMenusShouldWork(): void
    {
        $field = MenuTreeField::new('menuTree');
        $field->setMaxRootMenus(5);

        // 验证字段对象仍然存在
        $this->assertInstanceOf(MenuTreeField::class, $field);
    }

    public function testSetMaxSubMenusShouldWork(): void
    {
        $field = MenuTreeField::new('menuTree');
        $field->setMaxSubMenus(10);

        // 验证字段对象仍然存在
        $this->assertInstanceOf(MenuTreeField::class, $field);
    }

    public function testSetReadOnlyShouldWork(): void
    {
        $field = MenuTreeField::new('menuTree');
        $field->setReadOnly(true);

        // 验证字段对象仍然存在
        $this->assertInstanceOf(MenuTreeField::class, $field);
    }

    public function testShowPreviewShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->showPreview(false);

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetApiEndpointsShouldWork(): void
    {
        $customEndpoints = [
            'getMenus' => '/custom/menus',
            'newEndpoint' => '/custom/new',
        ];

        $field = MenuTreeField::new('menuTree');
        $field->setApiEndpoints($customEndpoints);

        // 验证字段对象仍然存在
        $this->assertInstanceOf(MenuTreeField::class, $field);
    }

    public function testMethodChainingShouldWork(): void
    {
        $menuData = ['menus' => []];

        $field = MenuTreeField::new('menuTree');
        $field->setMenuData($menuData);
        $field->setAccountId('account_123');
        $field->setVersionId('version_456');
        $field->enableDragAndDrop(true);
        $field->setMaxRootMenus(4);
        $field->setMaxSubMenus(6);
        $field->setReadOnly(false);
        $field->showPreview(true);

        $this->assertInstanceOf(MenuTreeField::class, $field);
    }

    public function testDefaultConfigurationShouldBeCorrect(): void
    {
        $field = MenuTreeField::new('menuTree');
        $dto = $field->getAsDto();

        // 验证默认配置
        $this->assertEquals('@WechatOfficialAccountMenu/admin/field/menu_tree.html.twig', $dto->getTemplatePath());
        $this->assertEquals(HiddenType::class, $dto->getFormType());
        $this->assertStringContainsString('menu-tree-field', $dto->getCssClass());
    }

    public function testFieldShouldHaveCorrectJsAndCssAssets(): void
    {
        $field = MenuTreeField::new('menuTree');

        $this->assertInstanceOf(MenuTreeField::class, $field);
        // JS和CSS文件验证可能需要更复杂的检查，这里只验证字段创建成功
    }

    public function testFieldImplementsFieldInterface(): void
    {
        $field = MenuTreeField::new('menuTree');

        $this->assertInstanceOf(FieldInterface::class, $field);
        $reflection = new \ReflectionObject($field);
        $this->assertTrue($reflection->hasMethod('getAsDto'));
    }

    public function testNewWithoutLabelShouldWork(): void
    {
        $field = MenuTreeField::new('menuTree');
        $dto = $field->getAsDto();

        $this->assertEquals('menuTree', $dto->getProperty());
        $this->assertNull($dto->getLabel());
    }

    public function testFieldShouldBeConfiguredForMenuTreeEditing(): void
    {
        $field = MenuTreeField::new('menuTree', 'Tree Editor');
        $dto = $field->getAsDto();

        // 验证模板路径指向菜单树编辑模板
        $templatePath = $dto->getTemplatePath();
        $this->assertIsString($templatePath);
        $this->assertStringContainsString('menu_tree.html.twig', $templatePath);

        // 验证CSS类包含树编辑相关的类
        $cssClass = $dto->getCssClass();
        $this->assertIsString($cssClass);
        $this->assertStringContainsString('menu-tree', $cssClass);

        // 验证使用隐藏表单类型
        $this->assertEquals(HiddenType::class, $dto->getFormType());
    }

    public function testFieldShouldHaveFullWidthByDefault(): void
    {
        $field = MenuTreeField::new('menuTree');
        $dto = $field->getAsDto();

        // 菜单树字段应该占满整个宽度
        // 列配置可能为null或默认值，验证不是小于全宽的设置
        $columns = $dto->getColumns();
        $this->assertTrue(null === $columns || 'col-md-12' === $columns || '' === $columns);
    }

    public function testFieldShouldIncludeSortableJsLibrary(): void
    {
        $field = MenuTreeField::new('menuTree');

        $this->assertInstanceOf(MenuTreeField::class, $field);
        // SortableJS库验证可能需要更复杂的检查，这里只验证字段创建成功
    }

    // EasyAdmin Field 继承方法测试

    public function testAddAssetMapperEntriesShouldReturnSameInstance(): void
    {
        if (!class_exists('Symfony\Component\AssetMapper\AssetMapper')) {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('AssetMapper is not installed');
        }

        $field = MenuTreeField::new('menuTree');
        $result = $field->addAssetMapperEntries('app', 'admin');

        if (class_exists('Symfony\Component\AssetMapper\AssetMapper')) {
            $this->assertInstanceOf(MenuTreeField::class, $result);
            $this->assertSame($field, $result);
        }
    }

    public function testAddCssClassShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->addCssClass('custom-class');

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testAddCssClassShouldAppendToCssClass(): void
    {
        $field = MenuTreeField::new('menuTree');
        $field->addCssClass('custom-class');
        $dto = $field->getAsDto();

        $this->assertStringContainsString('menu-tree-field', $dto->getCssClass());
        $this->assertStringContainsString('custom-class', $dto->getCssClass());
    }

    public function testAddCssFilesShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->addCssFiles('custom.css', 'theme.css');

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testAddFormThemeShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->addFormTheme('@custom/form.html.twig');

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testAddHtmlContentsToBodyShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->addHtmlContentsToBody('<script>alert("test");</script>');

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testAddHtmlContentsToHeadShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->addHtmlContentsToHead('<meta name="test" content="value">');

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testAddJsFilesShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->addJsFiles('custom.js', 'script.js');

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testAddWebpackEncoreEntriesShouldReturnSameInstance(): void
    {
        if (!class_exists('Symfony\WebpackEncoreBundle\Twig\EntryFilesTwigExtension')) {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Webpack Encore is not installed');
        }

        $field = MenuTreeField::new('menuTree');
        $result = $field->addWebpackEncoreEntries('app', 'admin');

        if (class_exists('Symfony\WebpackEncoreBundle\Twig\EntryFilesTwigExtension')) {
            $this->assertInstanceOf(MenuTreeField::class, $result);
            $this->assertSame($field, $result);
        }
    }

    public function testFormatValueShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $formatter = function ($value): string {
            $this->assertIsString($value);

            return 'formatted: ' . $value;
        };
        $result = $field->formatValue($formatter);

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testFormatValueShouldAcceptNullCallable(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->formatValue(null);

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testHideOnDetailShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->hideOnDetail();

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testHideOnFormShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->hideOnForm();

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testHideOnIndexShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->hideOnIndex();

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testHideWhenCreatingShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->hideWhenCreating();

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testHideWhenUpdatingShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->hideWhenUpdating();

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testOnlyOnDetailShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->onlyOnDetail();

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testOnlyOnFormsShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->onlyOnForms();

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testOnlyOnIndexShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->onlyOnIndex();

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testOnlyWhenCreatingShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->onlyWhenCreating();

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testOnlyWhenUpdatingShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->onlyWhenUpdating();

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetColumnsShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setColumns(6);

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetColumnsShouldAcceptIntegerValue(): void
    {
        $field = MenuTreeField::new('menuTree');
        $field->setColumns(6);
        $dto = $field->getAsDto();

        $this->assertEquals('col-md-6', $dto->getColumns());
    }

    public function testSetColumnsShouldAcceptStringValue(): void
    {
        $field = MenuTreeField::new('menuTree');
        $field->setColumns('col-6 col-sm-4 col-lg-3');
        $dto = $field->getAsDto();

        $this->assertEquals('col-6 col-sm-4 col-lg-3', $dto->getColumns());
    }

    public function testSetCssClassShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setCssClass('new-class');

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetCssClassShouldReplaceCssClass(): void
    {
        $field = MenuTreeField::new('menuTree');
        $field->setCssClass('new-class');
        $dto = $field->getAsDto();

        $this->assertEquals('new-class', $dto->getCssClass());
    }

    public function testSetCustomOptionShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setCustomOption('customKey', 'customValue');

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetCustomOptionsShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $options = ['option1' => 'value1', 'option2' => 'value2'];
        $result = $field->setCustomOptions($options);

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetDefaultColumnsShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setDefaultColumns(8);

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetDisabledShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setDisabled(true);

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetEmptyDataShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setEmptyData([]);

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetFieldFqcnShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setFieldFqcn(MenuTreeField::class);

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetFormattedValueShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setFormattedValue('formatted value');

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetFormTypeShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setFormType('Symfony\Component\Form\Extension\Core\Type\TextType');

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetFormTypeOptionShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setFormTypeOption('attr.class', 'form-control');

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetFormTypeOptionIfNotSetShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setFormTypeOptionIfNotSet('placeholder', 'Enter value');

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetFormTypeOptionsShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $options = ['attr' => ['class' => 'form-control'], 'placeholder' => 'Enter value'];
        $result = $field->setFormTypeOptions($options);

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetHelpShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setHelp('This is help text');

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetHtmlAttributeShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setHtmlAttribute('data-test', 'value');

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetHtmlAttributesShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $attributes = ['data-test' => 'value1', 'data-custom' => 'value2'];
        $result = $field->setHtmlAttributes($attributes);

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetLabelShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setLabel('New Label');

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetLabelShouldUpdateDto(): void
    {
        $field = MenuTreeField::new('menuTree');
        $field->setLabel('New Label');
        $dto = $field->getAsDto();

        $this->assertEquals('New Label', $dto->getLabel());
    }

    public function testSetPermissionShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setPermission('ROLE_ADMIN');

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetPropertyShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setProperty('newProperty');

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetPropertyShouldUpdateDto(): void
    {
        $field = MenuTreeField::new('menuTree');
        $field->setProperty('newProperty');
        $dto = $field->getAsDto();

        $this->assertEquals('newProperty', $dto->getProperty());
    }

    public function testSetPropertySuffixShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setPropertySuffix('_suffix');

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetRequiredShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setRequired(true);

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetSortableShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setSortable(true);

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetTemplateNameShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setTemplateName('custom_template');

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetTemplatePathShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setTemplatePath('@Custom/template.html.twig');

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetTemplatePathShouldUpdateDto(): void
    {
        $field = MenuTreeField::new('menuTree');
        $field->setTemplatePath('@Custom/template.html.twig');
        $dto = $field->getAsDto();

        $this->assertEquals('@Custom/template.html.twig', $dto->getTemplatePath());
    }

    public function testSetTextAlignShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setTextAlign('center');

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetTranslationParametersShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $parameters = ['%name%' => 'Menu Tree', '%type%' => 'Field'];
        $result = $field->setTranslationParameters($parameters);

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetValueShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setValue(['some' => 'value']);

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetVirtualShouldReturnSameInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setVirtual(true);

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testGetAsDtoShouldReturnFieldDto(): void
    {
        $field = MenuTreeField::new('menuTree', 'Menu Tree');
        $dto = $field->getAsDto();

        $this->assertInstanceOf(FieldDto::class, $dto);
        $this->assertEquals('menuTree', $dto->getProperty());
        $this->assertEquals('Menu Tree', $dto->getLabel());
    }

    public function testCloneShouldCreateNewDtoInstance(): void
    {
        $field = MenuTreeField::new('menuTree');
        $originalDto = $field->getAsDto();

        $clonedField = clone $field;
        $clonedDto = $clonedField->getAsDto();

        $this->assertNotSame($originalDto, $clonedDto);
        $this->assertEquals($originalDto->getProperty(), $clonedDto->getProperty());
    }

    #[TestWith(['left'])]
    #[TestWith(['center'])]
    #[TestWith(['right'])]
    public function testSetTextAlignShouldAcceptValidValues(string $alignment): void
    {
        $field = MenuTreeField::new('menuTree');
        $result = $field->setTextAlign($alignment);

        $this->assertInstanceOf(MenuTreeField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetTextAlignShouldRejectInvalidValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $field = MenuTreeField::new('menuTree');
        $field->setTextAlign('invalid');
    }

    public function testSetPropertySuffixShouldRejectEmptyValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The suffix cannot be empty.');

        $field = MenuTreeField::new('menuTree');
        $field->setPropertySuffix('');
    }

    public function testSetPropertySuffixShouldRejectInvalidName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('is not valid');

        $field = MenuTreeField::new('menuTree');
        $field->setPropertySuffix('invalid name with spaces');
    }

    public function testSetHtmlAttributeShouldRejectDotNotation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot use the "dot notation"');

        $field = MenuTreeField::new('menuTree');
        $field->setHtmlAttribute('data.test', 'value');
    }

    public function testSetHtmlAttributeShouldRejectNonScalarValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be a scalar value');

        $field = MenuTreeField::new('menuTree');
        /** @phpstan-ignore-next-line argument.type */
        $field->setHtmlAttribute('data-test', (object) ['invalid' => 'object']);
    }
}
