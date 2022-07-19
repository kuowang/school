<?php
/**
 * @author  Eddy <cumtsjh@163.com>
 */

namespace App\Repository\Admin;

use App\Model\Admin\EntityField;
use App\Repository\Searchable;

class EntityFieldRepository
{
    use Searchable;

    public static function list($perPage, $condition = [])
    {
        $data = EntityField::query()
            ->where(function ($query) use ($condition) {
                Searchable::buildQuery($query, $condition);
            })
            ->with('entity')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
        $formTypes = config('light.form_type');
        $data->transform(function ($item) use ($formTypes) {
            xssFilter($item);
            $item->entityName = $item->entity->name;
            $item->form_type = $formTypes[$item->form_type];
            $item->editUrl = route('admin::entityField.edit', ['id' => $item->id]) . '?entity_id=' . $item->entity->id;
            $item->deleteUrl = route('admin::entityField.delete', ['id' => $item->id]);
            return $item;
        });

        return [
            'code' => 0,
            'msg' => '',
            'count' => $data->total(),
            'data' => $data->items(),
        ];
    }

    public static function add($data)
    {
        return EntityField::query()->create($data);
    }

    public static function update($id, $data)
    {
        return EntityField::query()->where('id', $id)->update($data);
    }

    public static function find($id)
    {
        return EntityField::query()->find($id);
    }

    public static function delete($id)
    {
        return EntityField::destroy($id);
    }

    public static function getByEntityId($id)
    {
        return  EntityField::query()->where('entity_id', $id)
            ->orderBy('order')->orderBy('is_show_inline')->get();
    }

    public static function getFields($entityId)
    {
        return  EntityField::query()->select('name')->where('entity_id', $entityId)
            ->pluck('name')->toArray();
    }

    public static function getSaveFields($entityId)
    {
        return  EntityField::query()->select('name')->where('entity_id', $entityId)
            ->whereNotIn('form_type', ['inputTags'])->pluck('name')->toArray();
    }

    public static function getUpdateFields($entityId)
    {
        return  EntityField::query()->select('name', 'form_type')->where('entity_id', $entityId)
            ->where('is_edit', EntityField::EDIT_ENABLE)
            ->whereNotIn('form_type', ['inputTags'])
            ->pluck('form_type', 'name')->toArray();
    }

    public static function getInputTagsField($entityId)
    {
        return EntityField::query()->where('entity_id', $entityId)
            ->where('form_type', 'inputTags')
            ->first();
    }

    public static function formTypeBeUnique($formType)
    {
        return in_array($formType, ['inputTags'], true);
    }

    /**
     * 获取指定字段的枚举值，展示 select 表单用
     *
     * @param int $entityId 模型ID
     * @param string $fieldName 字段名
     * @return array
     */
    public static function formEnums(int $entityId, string $fieldName): array
    {
        $field = EntityField::query()
            ->where('entity_id', $entityId)
            ->where('name', $fieldName)
            ->first();
        if (!$field) {
            throw new \InvalidArgumentException('字段不存在：{$fieldName}');
        }
        $fieldArr = parseEntityFieldParams($field->form_params);
        $enums = [];
        foreach ($fieldArr as $v) {
            $enums[$v[0]] = $v[1];
        }
        return $enums;
    }

    /**
     * 获取列表中展示字段
     *
     * @param int $entityId 实体ID
     * @return array
     */
    public static function listDisplayFields(int $entityId): array
    {
        return  EntityField::query()
            ->select('name', 'form_name')
            ->where('entity_id', $entityId)
            ->where('is_list_display', EntityField::SHOW_LIST)
            ->orderBy('list_sort')
            ->orderBy('id')
            ->pluck('form_name', 'name')->toArray();
    }

    /**
     * 获取指定模型的搜索项配置
     *
     * @param int $entityId
     * @return array
     */
    public static function searchableFields(int $entityId): array
    {
        $searchField = [];
        EntityField::query()
            ->where('entity_id', $entityId)
            ->where('is_enable_search', EntityField::SEARCH_ENABLE)
            ->orderBy('list_sort')
            ->get()
            ->each(function ($item) use (&$searchField, $entityId) {
                $searchField[$item->name] = [
                    'showType' => $item->show_type,
                    'searchType' => $item->search_type,
                    'title' => $item->form_name,
                ];
                if ($item->show_type === 'select') {
                    $searchField[$item->name]['enums'] = $item->form_type === 'reference_category' ?
                        CategoryRepository::idMapNameArr($entityId) :
                        array_column(parseEntityFieldParams($item->form_params), 1, 0);
                }
            });
        return $searchField;
    }
}
