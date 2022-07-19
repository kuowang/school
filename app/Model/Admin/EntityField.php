<?php
/**
 * @author  Eddy <cumtsjh@163.com>
 */

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntityField extends Model
{
    const SHOW_ENABLE = 1;
    const SHOW_DISABLE = 0;

    const SHOW_INLINE = 1;
    const SHOW_NOT_INLINE = 0;

    const EDIT_ENABLE = 1;
    const EDIT_DISABLE = 0;

    const REQUIRED_ENABLE = 1;
    const REQUIRED_DISABLE = 0;

    const SHOW_LIST = 1;
    const SHOW_NOT_LIST = 0;

    const SEARCH_ENABLE = 1;
    const SEARCH_DISABLE = 0;

    protected $guarded = [];

    public function entity(): BelongsTo
    {
        return $this->belongsTo('App\Model\Admin\Entity', 'entity_id');
    }

    public static $listField = [
        'entityName' => '模型',
        'name' => '字段名称',
        'type' => '字段类型',
        'form_name' => '表单名称',
        'form_type' => ['title' => '表单类型', 'sort' => true],
        'is_show_inline' => [
            'title' => '行内展示', 'sort' => true, 'templet' => '#isShowInlineTemplet', 'event' => 'showInlineEvent'
        ],
        'is_show' => ['title' => '显示', 'templet' => '#isShowTemplet', 'event' => 'showEvent'],
        'is_list_display' => ['title' => '列表显示', 'templet' => '#isShowList', 'event' => 'showListEvent'],
        'order' => ['title' => '表单排序', 'sort' => true, 'edit' => true, 'width' => 100],
        'list_sort' => ['title' => '列表排序', 'sort' => true, 'edit' => true, 'width' => 100],
    ];

    public static $searchField = [
        'name' => '字段名称',
        'entity_id' => [
            'title' => '模型',
            'searchType' => '=',
            'showType' => 'select',
            'enums' => [],
        ]
    ];
}
