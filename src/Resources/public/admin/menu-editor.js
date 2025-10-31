/**
 * 微信公众号菜单管理主脚本
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // 初始化菜单类型字段显示/隐藏
        initMenuTypeFields();
        
        // 初始化工具提示
        $('[data-toggle="tooltip"]').tooltip();
        
        // 处理菜单同步确认
        $('.action-syncToWechat').on('click', function(e) {
            if (!confirm('确定要将当前菜单同步到微信吗？这将覆盖微信端的现有菜单。')) {
                e.preventDefault();
            }
        });
        
        // 处理版本发布确认
        $('.action-publish').on('click', function(e) {
            if (!confirm('确定要发布此版本到微信吗？这将替换当前的线上菜单。')) {
                e.preventDefault();
            }
        });
    });
    
    function initMenuTypeFields() {
        const $typeSelect = $('select[name*="[type]"]');
        
        if ($typeSelect.length === 0) return;
        
        function toggleTypeFields() {
            const selectedType = $typeSelect.val();
            
            // 隐藏所有类型相关字段
            $('[data-show-when-type]').closest('.form-group').hide();
            
            // 显示当前类型相关字段
            if (selectedType) {
                $(`[data-show-when-type="${selectedType}"]`).closest('.form-group').show();
                $('[data-show-when-type*="' + selectedType + '"]').each(function() {
                    const types = $(this).data('show-when-type').split(',');
                    if (types.includes(selectedType)) {
                        $(this).closest('.form-group').show();
                    }
                });
            }
        }
        
        // 绑定事件
        $typeSelect.on('change', toggleTypeFields);
        
        // 初始化
        toggleTypeFields();
    }
    
})(jQuery);