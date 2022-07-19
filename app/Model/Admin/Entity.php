<?php
/**
 * @author  Eddy <cumtsjh@163.com>
 */

namespace App\Model\Admin;

class Entity extends Model
{
    const COMMENT_ENABLE = 1;
    const COMMENT_DISABLE = 0;

    const INTERNAL_YES = 1;
    const INTERNAL_NO = 0;

    const CONTENT_MANAGE_YES = 1;
    const CONTENT_MANAGE_NO = 0;

    protected $guarded = [];

    public static $listField = [
        'name' => '名称',
        'table_name' => '数据库表名',
        'description' => '描述',
        'is_show_content_manage' => ['title' => '列表显示', 'templet' => '#isShowContentManage', 'event' => 'showContentManageEvent'],
        'sort' => ['title' => '排序', 'sort' => true, 'edit' => true, 'width' => 80],
    ];

    public function fields()
    {
        return $this->hasMany('App\Model\Admin\EntityField', 'entity_id');
    }

    /**
     * 限制查询外部模型
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExternal($query)
    {
        return $query->where('is_internal', self::INTERNAL_NO);
    }
}
