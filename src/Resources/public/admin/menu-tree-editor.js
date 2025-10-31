/**
 * 微信公众号菜单树形编辑器
 */
(function($) {
    'use strict';

    class MenuTreeEditor {
        constructor(wrapper) {
            this.$wrapper = $(wrapper);
            this.accountId = this.$wrapper.data('account-id');
            this.versionId = this.$wrapper.data('version-id');
            this.isDragDrop = this.$wrapper.data('drag-drop') === 'true';
            this.maxRootMenus = parseInt(this.$wrapper.data('max-root')) || 3;
            this.maxSubMenus = parseInt(this.$wrapper.data('max-sub')) || 5;
            this.isReadOnly = this.$wrapper.data('readonly') === 'true';
            this.endpoints = this.$wrapper.data('endpoints') || {};
            
            this.$loading = this.$wrapper.find('.menu-tree-loading');
            this.$content = this.$wrapper.find('.menu-tree-content');
            this.$empty = this.$wrapper.find('.menu-tree-empty');
            this.$root = this.$wrapper.find('.menu-tree-root');
            this.$hiddenInput = this.$wrapper.find('input[type="hidden"]');
            
            this.menuData = [];
            this.sortables = [];
            
            this.init();
        }
        
        init() {
            this.loadMenuData();
            this.bindEvents();
            this.initTemplating();
        }
        
        initTemplating() {
            // 简单的模板引擎
            this.template = function(templateId, data) {
                let html = $('#' + templateId).html();
                
                // 处理条件语句
                html = html.replace(/{{#if\s+(\w+)}}([\s\S]*?){{\/if}}/g, function(match, prop, content) {
                    return data[prop] ? content : '';
                });
                
                html = html.replace(/{{#unless\s+(\w+)}}([\s\S]*?){{\/unless}}/g, function(match, prop, content) {
                    return !data[prop] ? content : '';
                });
                
                // 处理变量
                html = html.replace(/{{(\w+)}}/g, function(match, prop) {
                    return data[prop] || '';
                });
                
                return html;
            };
        }
        
        loadMenuData() {
            if (!this.versionId) {
                this.renderMenuTree([]);
                return;
            }
            
            const url = this.endpoints.getMenus.replace('{versionId}', this.versionId);
            
            $.ajax({
                url: url,
                method: 'GET',
                success: (data) => {
                    this.menuData = data;
                    this.renderMenuTree(data);
                },
                error: (xhr) => {
                    this.showError('加载菜单失败：' + xhr.responseJSON?.error || '未知错误');
                }
            });
        }
        
        renderMenuTree(menus) {
            this.$loading.hide();
            
            if (!menus || menus.length === 0) {
                this.$content.hide();
                this.$empty.show();
                return;
            }
            
            this.$empty.hide();
            this.$content.show();
            
            this.$root.empty();
            
            // 渲染一级菜单
            const rootMenus = menus.filter(m => !m.parentId).sort((a, b) => a.position - b.position);
            
            rootMenus.forEach(menu => {
                const $menuItem = this.renderMenuItem(menu, menus);
                this.$root.append($menuItem);
            });
            
            // 初始化拖拽
            if (this.isDragDrop && !this.isReadOnly) {
                this.initSortable();
            }
            
            // 更新隐藏字段
            this.updateHiddenField();
        }
        
        renderMenuItem(menu, allMenus) {
            const subMenus = allMenus.filter(m => m.parentId === menu.id).sort((a, b) => a.position - b.position);
            const hasChildren = subMenus.length > 0;
            const canAddSub = !menu.parentId && subMenus.length < this.maxSubMenus;
            
            const menuTypeLabels = {
                'click': '点击',
                'view': '链接',
                'miniprogram': '小程序',
                'scancode_push': '扫码',
                'scancode_waitmsg': '扫码等待',
                'pic_sysphoto': '拍照',
                'pic_photo_or_album': '拍照或相册',
                'pic_weixin': '微信相册',
                'location_select': '位置',
                'media_id': '素材',
                'view_limited': '图文'
            };
            
            const data = {
                id: menu.id,
                parentId: menu.parentId || '',
                name: menu.name,
                typeLabel: menuTypeLabels[menu.type] || menu.type,
                enabled: menu.enabled,
                hasChildren: hasChildren,
                canAddSub: canAddSub,
                readonly: this.isReadOnly
            };
            
            const $item = $(this.template('menu-item-template', data));
            
            // 渲染子菜单
            if (hasChildren) {
                const $children = $item.find('.menu-item-children');
                subMenus.forEach(subMenu => {
                    const $subItem = this.renderMenuItem(subMenu, allMenus);
                    $children.append($subItem);
                });
            }
            
            return $item;
        }
        
        initSortable() {
            // 销毁旧的实例
            this.sortables.forEach(s => s.destroy());
            this.sortables = [];
            
            // 一级菜单排序
            const rootSortable = Sortable.create(this.$root[0], {
                group: 'menu',
                handle: '.menu-item-handle',
                animation: 150,
                fallbackOnBody: true,
                swapThreshold: 0.65,
                onEnd: (evt) => {
                    this.updatePositions();
                }
            });
            this.sortables.push(rootSortable);
            
            // 子菜单排序
            this.$wrapper.find('.menu-item-children').each((index, el) => {
                const sortable = Sortable.create(el, {
                    group: 'submenu',
                    handle: '.menu-item-handle',
                    animation: 150,
                    fallbackOnBody: true,
                    swapThreshold: 0.65,
                    onEnd: (evt) => {
                        this.updatePositions();
                    }
                });
                this.sortables.push(sortable);
            });
        }
        
        bindEvents() {
            // 添加一级菜单
            this.$wrapper.on('click', '.add-root-menu', () => {
                const rootCount = this.$root.children().length;
                if (rootCount >= this.maxRootMenus) {
                    this.showError(`一级菜单最多只能有${this.maxRootMenus}个！`);
                    return;
                }
                this.editMenu();
            });
            
            // 添加子菜单
            this.$wrapper.on('click', '.add-sub-menu', (e) => {
                const $menuItem = $(e.currentTarget).closest('.menu-item');
                const parentId = $menuItem.data('menu-id');
                const subCount = $menuItem.find('> .menu-item-children > .menu-item').length;
                
                if (subCount >= this.maxSubMenus) {
                    this.showError(`每个一级菜单最多只能有${this.maxSubMenus}个子菜单！`);
                    return;
                }
                
                this.editMenu(null, parentId);
            });
            
            // 编辑菜单
            this.$wrapper.on('click', '.edit-menu', (e) => {
                const $menuItem = $(e.currentTarget).closest('.menu-item');
                const menuId = $menuItem.data('menu-id');
                const menu = this.findMenuById(menuId);
                this.editMenu(menu);
            });
            
            // 删除菜单
            this.$wrapper.on('click', '.delete-menu', (e) => {
                const $menuItem = $(e.currentTarget).closest('.menu-item');
                const menuId = $menuItem.data('menu-id');
                const menu = this.findMenuById(menuId);
                
                if (menu.children && menu.children.length > 0) {
                    this.showError('请先删除子菜单！');
                    return;
                }
                
                if (confirm(`确定要删除菜单"${menu.name}"吗？`)) {
                    this.deleteMenu(menuId);
                }
            });
            
            // 展开/收起
            this.$wrapper.on('click', '.toggle-children', (e) => {
                const $btn = $(e.currentTarget);
                const $menuItem = $btn.closest('.menu-item');
                const $children = $menuItem.find('> .menu-item-children');
                
                $children.slideToggle(200);
                $btn.find('i').toggleClass('fa-chevron-down fa-chevron-up');
            });
            
            // 展开全部
            this.$wrapper.on('click', '.expand-all', () => {
                this.$wrapper.find('.menu-item-children').slideDown(200);
                this.$wrapper.find('.toggle-children i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
            });
            
            // 收起全部
            this.$wrapper.on('click', '.collapse-all', () => {
                this.$wrapper.find('.menu-item-children').slideUp(200);
                this.$wrapper.find('.toggle-children i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            });
            
            // 保存菜单按钮
            $('#saveMenuBtn').on('click', () => {
                this.saveMenu();
            });
            
            // 菜单类型切换
            $('#menuEditForm select[name="type"]').on('change', function() {
                const type = $(this).val();
                $('.menu-type-field').hide();
                $(`.menu-type-field[data-types*="${type}"]`).show();
            });
        }
        
        editMenu(menu = null, parentId = null) {
            const $modal = $('#menuEditModal');
            const $form = $('#menuEditForm');
            
            // 重置表单
            $form[0].reset();
            $('.menu-type-field').hide();
            
            if (menu) {
                // 编辑模式
                $modal.find('.modal-title').text('编辑菜单');
                $form.find('input[name="id"]').val(menu.id);
                $form.find('input[name="parentId"]').val(menu.parentId || '');
                $form.find('input[name="name"]').val(menu.name);
                $form.find('select[name="type"]').val(menu.type).trigger('change');
                $form.find('input[name="clickKey"]').val(menu.clickKey || '');
                $form.find('input[name="url"]').val(menu.url || '');
                $form.find('input[name="appId"]').val(menu.appId || '');
                $form.find('textarea[name="pagePath"]').val(menu.pagePath || '');
                $form.find('input[name="mediaId"]').val(menu.mediaId || '');
                $form.find('input[name="position"]').val(menu.position || 0);
                $form.find('input[name="enabled"]').prop('checked', menu.enabled !== false);
            } else {
                // 新建模式
                $modal.find('.modal-title').text('新建菜单');
                $form.find('input[name="id"]').val('');
                $form.find('input[name="parentId"]').val(parentId || '');
            }
            
            $modal.modal('show');
        }
        
        saveMenu() {
            const $form = $('#menuEditForm');
            const formData = {
                id: $form.find('input[name="id"]').val(),
                parentId: $form.find('input[name="parentId"]').val() || null,
                name: $form.find('input[name="name"]').val(),
                type: $form.find('select[name="type"]').val(),
                clickKey: $form.find('input[name="clickKey"]').val() || null,
                url: $form.find('input[name="url"]').val() || null,
                appId: $form.find('input[name="appId"]').val() || null,
                pagePath: $form.find('textarea[name="pagePath"]').val() || null,
                mediaId: $form.find('input[name="mediaId"]').val() || null,
                position: parseInt($form.find('input[name="position"]').val()) || 0,
                enabled: $form.find('input[name="enabled"]').is(':checked')
            };
            
            // 验证
            if (!formData.name) {
                alert('请输入菜单名称！');
                return;
            }
            
            if (!formData.type) {
                alert('请选择菜单类型！');
                return;
            }
            
            // 根据类型验证必填字段
            if (formData.type === 'view' && !formData.url) {
                alert('跳转URL不能为空！');
                return;
            }
            
            if (formData.type === 'click' && !formData.clickKey) {
                alert('菜单KEY不能为空！');
                return;
            }
            
            if (formData.type === 'miniprogram' && (!formData.appId || !formData.pagePath)) {
                alert('小程序AppID和页面路径不能为空！');
                return;
            }
            
            const isNew = !formData.id;
            const url = isNew 
                ? this.endpoints.createMenu.replace('{versionId}', this.versionId)
                : this.endpoints.saveMenu.replace('{versionId}', this.versionId).replace('{menuId}', formData.id);
            
            $.ajax({
                url: url,
                method: isNew ? 'POST' : 'PUT',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                success: (data) => {
                    $('#menuEditModal').modal('hide');
                    this.showSuccess(isNew ? '菜单创建成功！' : '菜单更新成功！');
                    this.loadMenuData();
                },
                error: (xhr) => {
                    this.showError('保存失败：' + xhr.responseJSON?.error || '未知错误');
                }
            });
        }
        
        deleteMenu(menuId) {
            const url = this.endpoints.deleteMenu
                .replace('{versionId}', this.versionId)
                .replace('{menuId}', menuId);
            
            $.ajax({
                url: url,
                method: 'DELETE',
                success: () => {
                    this.showSuccess('菜单删除成功！');
                    this.loadMenuData();
                },
                error: (xhr) => {
                    this.showError('删除失败：' + xhr.responseJSON?.error || '未知错误');
                }
            });
        }
        
        updatePositions() {
            const positions = {};
            
            // 收集所有菜单的新位置
            this.$wrapper.find('.menu-item').each((index, el) => {
                const $item = $(el);
                const menuId = $item.data('menu-id');
                const $parent = $item.parent().closest('.menu-item');
                const parentId = $parent.length ? $parent.data('menu-id') : null;
                const position = $item.index();
                
                positions[menuId] = {
                    parentId: parentId,
                    position: position
                };
            });
            
            const url = this.endpoints.updatePositions.replace('{versionId}', this.versionId);
            
            $.ajax({
                url: url,
                method: 'POST',
                data: JSON.stringify({ positions: positions }),
                contentType: 'application/json',
                success: () => {
                    this.showSuccess('排序已更新！');
                    this.updateHiddenField();
                },
                error: (xhr) => {
                    this.showError('更新排序失败：' + xhr.responseJSON?.error || '未知错误');
                    this.loadMenuData(); // 重新加载恢复原始顺序
                }
            });
        }
        
        updateHiddenField() {
            // 构建菜单树结构
            const menuTree = this.buildMenuTree();
            this.$hiddenInput.val(JSON.stringify(menuTree));
        }
        
        buildMenuTree() {
            const tree = [];
            
            this.$root.children('.menu-item').each((index, el) => {
                const $item = $(el);
                const menuId = $item.data('menu-id');
                const menu = this.findMenuById(menuId);
                
                if (menu) {
                    const node = {
                        ...menu,
                        children: []
                    };
                    
                    // 添加子菜单
                    $item.find('> .menu-item-children > .menu-item').each((subIndex, subEl) => {
                        const subMenuId = $(subEl).data('menu-id');
                        const subMenu = this.findMenuById(subMenuId);
                        if (subMenu) {
                            node.children.push(subMenu);
                        }
                    });
                    
                    tree.push(node);
                }
            });
            
            return tree;
        }
        
        findMenuById(menuId) {
            return this.menuData.find(m => m.id === menuId);
        }
        
        showSuccess(message) {
            // 使用 EasyAdmin 的通知系统
            if (window.toastr) {
                toastr.success(message);
            } else {
                alert(message);
            }
        }
        
        showError(message) {
            if (window.toastr) {
                toastr.error(message);
            } else {
                alert(message);
            }
        }
    }
    
    // jQuery 插件
    $.fn.menuTreeEditor = function() {
        return this.each(function() {
            if (!$(this).data('menuTreeEditor')) {
                $(this).data('menuTreeEditor', new MenuTreeEditor(this));
            }
        });
    };
    
    // 自动初始化
    $(document).ready(function() {
        $('.menu-tree-field-wrapper').menuTreeEditor();
    });
    
})(jQuery);