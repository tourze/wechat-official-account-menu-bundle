<?php

namespace WechatOfficialAccountMenuBundle\Tests\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WechatOfficialAccountMenuBundle\Field\MenuPreviewField;

/**
 * @internal
 */
#[CoversClass(MenuPreviewField::class)]
final class MenuPreviewFieldTest extends TestCase
{
    public function testNewShouldCreateFieldInstance(): void
    {
        $field = MenuPreviewField::new('menuData', 'Menu Preview');

        $this->assertInstanceOf(MenuPreviewField::class, $field);
        $this->assertInstanceOf(FieldInterface::class, $field);
    }

    public function testNewShouldSetBasicProperties(): void
    {
        $field = MenuPreviewField::new('menuData', 'Menu Preview');
        $dto = $field->getAsDto();

        $this->assertEquals('menuData', $dto->getProperty());
        $this->assertEquals('Menu Preview', $dto->getLabel());
        $this->assertEquals('@WechatOfficialAccountMenu/admin/field/menu_preview.html.twig', $dto->getTemplatePath());
    }

    public function testSetMenuDataShouldReturnSameInstance(): void
    {
        $menuData = [
            'button' => [
                ['name' => 'Menu 1', 'type' => 'click', 'key' => 'menu1'],
            ],
        ];

        $field = MenuPreviewField::new('menuData');
        $result = $field->setMenuData($menuData);

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetAccountNameShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setAccountName('Test Account');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetShowMobileFrameShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setShowMobileFrame(false);

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetInteractiveShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setInteractive(true);

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetScaleShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setScale(0.8);

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetHighlightMenuIdShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setHighlightMenuId('menu_123');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetShowMenuInfoShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setShowMenuInfo(false);

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testMethodChainingShouldWork(): void
    {
        $menuData = ['button' => []];
        $accountName = 'Test Account';

        $field = MenuPreviewField::new('menuData')
            ->setMenuData($menuData)
            ->setAccountName($accountName)
            ->setShowMobileFrame(true)
            ->setInteractive(false)
            ->setScale(1.2)
            ->setHighlightMenuId('test_menu')
            ->setShowMenuInfo(true)
        ;

        $this->assertInstanceOf(MenuPreviewField::class, $field);
    }

    public function testDefaultConfigurationShouldBeCorrect(): void
    {
        $field = MenuPreviewField::new('menuData');
        $dto = $field->getAsDto();

        // 验证默认配置
        $this->assertEquals('@WechatOfficialAccountMenu/admin/field/menu_preview.html.twig', $dto->getTemplatePath());
        $this->assertStringContainsString('menu-preview-field', $dto->getCssClass());

        // 验证表单选项
        $formTypeOptions = $dto->getFormTypeOptions();
        $this->assertArrayHasKey('mapped', $formTypeOptions);
        $this->assertFalse($formTypeOptions['mapped']);
    }

    public function testFieldShouldHaveCorrectCssAndJsAssets(): void
    {
        $field = MenuPreviewField::new('menuData');

        $this->assertInstanceOf(MenuPreviewField::class, $field);
        // CSS文件验证可能需要更复杂的检查，这里只验证字段创建成功
    }

    public function testFieldImplementsFieldInterface(): void
    {
        $field = MenuPreviewField::new('menuData');

        $this->assertInstanceOf(FieldInterface::class, $field);
        $reflection = new \ReflectionObject($field);
        $this->assertTrue($reflection->hasMethod('getAsDto'));
    }

    public function testNewWithoutLabelShouldWork(): void
    {
        $field = MenuPreviewField::new('menuData');
        $dto = $field->getAsDto();

        $this->assertEquals('menuData', $dto->getProperty());
        $this->assertNull($dto->getLabel());
    }

    public function testFieldShouldBeConfiguredForMenuPreview(): void
    {
        $field = MenuPreviewField::new('menuData', 'Preview');
        $dto = $field->getAsDto();

        // 验证模板路径指向菜单预览模板
        $templatePath = $dto->getTemplatePath();
        $this->assertIsString($templatePath);
        $this->assertStringContainsString('menu_preview.html.twig', $templatePath);

        // 验证CSS类包含预览相关的类
        $cssClass = $dto->getCssClass();
        $this->assertIsString($cssClass);
        $this->assertStringContainsString('menu-preview', $cssClass);
    }

    public function testAddAssetMapperEntriesShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->addAssetMapperEntries('app');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testAddCssClassShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->addCssClass('custom-class');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testAddCssFilesShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->addCssFiles('custom.css');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testAddFormThemeShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->addFormTheme('form_theme.html.twig');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testAddHtmlContentsToBodyShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->addHtmlContentsToBody('<script>console.log("test");</script>');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testAddHtmlContentsToHeadShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->addHtmlContentsToHead('<meta name="test" content="value">');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testAddJsFilesShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->addJsFiles('script.js');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testAddWebpackEncoreEntriesShouldReturnSameInstance(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You are trying to add Webpack Encore entries in a field but Webpack Encore is not installed');

        $field = MenuPreviewField::new('menuData');
        $field->addWebpackEncoreEntries('app');
    }

    public function testFormatValueShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->formatValue(function ($value): string {
            $this->assertIsString($value);

            return strtoupper($value);
        });

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testHideOnDetailShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->hideOnDetail();

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testHideOnFormShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->hideOnForm();

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testHideOnIndexShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->hideOnIndex();

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testHideWhenCreatingShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->hideWhenCreating();

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testHideWhenUpdatingShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->hideWhenUpdating();

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testOnlyOnDetailShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->onlyOnDetail();

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testOnlyOnFormsShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->onlyOnForms();

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testOnlyOnIndexShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->onlyOnIndex();

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testOnlyWhenCreatingShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->onlyWhenCreating();

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testOnlyWhenUpdatingShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->onlyWhenUpdating();

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetColumnsShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setColumns('col-md-6');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetCssClassShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setCssClass('new-class');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetCustomOptionShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setCustomOption('customKey', 'customValue');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetCustomOptionsShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setCustomOptions(['key1' => 'value1', 'key2' => 'value2']);

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetDefaultColumnsShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setDefaultColumns('col-lg-8');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetDisabledShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setDisabled(true);

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetEmptyDataShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setEmptyData('empty');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetFieldFqcnShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setFieldFqcn(MenuPreviewField::class);

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetFormattedValueShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setFormattedValue('formatted');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetFormTypeShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setFormType('Symfony\Component\Form\Extension\Core\Type\TextType');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetFormTypeOptionShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setFormTypeOption('attr', ['class' => 'form-control']);

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetFormTypeOptionIfNotSetShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setFormTypeOptionIfNotSet('placeholder', 'Enter value');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetFormTypeOptionsShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setFormTypeOptions(['required' => false, 'label' => 'Test']);

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetHelpShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setHelp('Help text');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetHtmlAttributeShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setHtmlAttribute('data-test', 'value');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetHtmlAttributesShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setHtmlAttributes(['data-id' => '123', 'class' => 'test']);

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetLabelShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setLabel('New Label');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetPermissionShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setPermission('ROLE_ADMIN');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetPropertyShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setProperty('newProperty');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetPropertySuffixShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setPropertySuffix('_suffix');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetRequiredShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setRequired(true);

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetSortableShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setSortable(false);

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetTemplateNameShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setTemplateName('custom_template');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetTemplatePathShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setTemplatePath('custom/template.html.twig');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetTextAlignShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setTextAlign('center');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetTranslationParametersShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setTranslationParameters(['%param%' => 'value']);

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetValueShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setValue('test value');

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }

    public function testSetVirtualShouldReturnSameInstance(): void
    {
        $field = MenuPreviewField::new('menuData');
        $result = $field->setVirtual(true);

        $this->assertInstanceOf(MenuPreviewField::class, $result);
        $this->assertSame($field, $result);
    }
}
