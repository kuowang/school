@extends('admin.base')

@section('content')
    @include('admin.breadcrumb')

    <div class="layui-card">
        <div class="layui-card-body">
            <table class="layui-table" lay-data="{url:'{{ route('admin::entity.list') }}?{{ request()->getQueryString() }}', page:true, limit:50, id:'test', toolbar:'<div><a href=\'{{ route('admin::entity.create') }}\'><i class=\'layui-icon layui-icon-add-1\'></i>新增模型</a></div>'}" lay-filter="test">
                <thead>
                <tr>
                    <th lay-data="{field:'id', width:80, sort: true}">ID</th>
                    @include('admin.listHead', ['data' => App\Model\Admin\Entity::$listField])
                    <th lay-data="{field:'created_at'}">添加时间</th>
                    <th lay-data="{field:'updated_at'}">更新时间</th>
                    <th lay-data="{width:350, templet:'#action'}">操作</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection
<script type="text/html" id="action">
    <a href="<% d.editUrl %>" class="layui-table-link" title="编辑"><i class="layui-icon layui-icon-edit"></i></a>
    <a href="javascript:;" class="layui-table-link" title="删除" style="margin-left: 10px" onclick="deleteEntity('<% d.deleteUrl %>')"><i class="layui-icon layui-icon-delete"></i></a>
    <a href="javascript:;" class="layui-table-link" title="复制" style="margin-left: 10px" onclick="copyEntity('<% d.copyUrl %>')"><i class="layui-icon layui-icon-file"></i></a>
    <a href="javascript:;" class="layui-table-link" title="添加默认菜单，用于精细化权限控制" style="margin-left: 10px" onclick="addEntityMenus('<% d.menuUrl %>')"><i class="layui-icon layui-icon-menu-fill"></i></a>
    <%#  if(d.enable_comment == {{ App\Model\Admin\Entity::COMMENT_ENABLE }}){ %> <a href="<% d.commentListUrl %>" class="layui-table-link" title="评论列表" style="margin-left: 5px"><i class="layui-icon layui-icon-reply-fill"></i></a> <%#  } %>
    <a href="<% d.fieldUrl %>" class="layui-table-link" title="字段管理" style="margin-left: 5px">字段管理</a>
    <a href="<% d.contentUrl %>" class="layui-table-link" title="字段管理" style="margin-left: 5px">内容管理</a>
</script>

<script type="text/html" id="isShowContentManage">
    <input data-id="<% d.id %>" type="checkbox" name="is_show_content_manage" lay-skin="switch" lay-text="是|否"
    <%# if (d.is_show_content_manage == 1) { %>
    checked
    <%# } %>
    >
</script>

@section('js')
    <script>
        var laytpl = layui.laytpl;
        laytpl.config({
            open: '<%',
            close: '%>'
        });

        var laydate = layui.laydate;
        laydate.render({
            elem: '#created_at',
            range: '~'
        });

        var table = layui.table;
        table.on('edit(test)', function(obj){ //注：edit是固定事件名，test是table原始容器的属性 lay-filter="对应的值"
            $.ajax({
                url: '{{ route('admin::entity.listUpdate', ['id' => '__replace_id']) }}'.replace('__replace_id', obj.data.id),
                method: 'put',
                dataType: 'json',
                data: {sort: obj.value},
                success: function (result) {
                    if (result.code !== 0) {
                        layer.msg(result.msg, {shift: 3});
                        return false;
                    }
                    layer.msg(result.msg, {icon: 1});
                }
            });
        });

        table.on('tool(test)', function (obj) {
            var event = obj.event, tr = obj.tr;
            var maps = {
                showContentManageEvent: "is_show_content_manage"
            };

            var key = maps[event];
            var val = tr.find("input[name='" + key + "']").prop('checked') ? 1 : 0;
            $.ajax({
                url: '{{ route('admin::entity.listUpdate', ['id' => '__replace_id']) }}'.replace('__replace_id', obj.data.id),
                method: 'put',
                dataType: 'json',
                data: {[key]: val},
                success: function (result) {
                    if (result.code !== 0) {
                        layer.msg(result.msg, {shift: 3});
                        return false;
                    }
                    layer.msg(result.msg, {icon: 1});
                }
            });
        });

        function deleteEntity (url) {
            layer.confirm('删除模型将删除模型相关的所有数据（模型、模型字段、模型分类、模型内容等），请谨慎操作！确定要删除？', function(index){
                layer.prompt({
                    formType: 1,
                    title: '请输入登录密码',
                }, function(value, index, elem){
                    $.ajax({
                        url: url,
                        data: {'_method': 'DELETE', 'password': value},
                        success: function (result) {
                            if (result.code !== 0) {
                                layer.msg(result.msg, {shift: 6});
                                return false;
                            }
                            layer.msg(result.msg, {icon: 1}, function () {
                                if (result.reload) {
                                    location.reload();
                                }
                                if (result.redirect) {
                                    location.href = '{!! url()->previous() !!}';
                                }
                            });
                        }
                    });
                    layer.close(index);
                });

                layer.close(index);
            });
        }

        function copyEntity (url) {
            layer.confirm('复制模型将新建一个和当前模型一样的模型（数据库表结构、表单定义等信息一致），确定要复制？', function(index){
                layer.prompt({
                    formType: 0,
                    title: '请输入新模型的数据库表名称',
                }, function(value, index, elem){
                    $.ajax({
                        url: url,
                        data: {'table_name': value},
                        success: function (result) {
                            if (result.code !== 0) {
                                layer.msg(result.msg, {shift: 6});
                                return false;
                            }
                            layer.msg(result.msg, {icon: 1}, function () {
                                if (result.reload) {
                                    location.reload();
                                }
                                if (result.redirect) {
                                    location.href = '{!! url()->previous() !!}';
                                }
                            });
                        }
                    });
                    layer.close(index);
                });

                layer.close(index);
            });
        }

        function addEntityMenus (url) {
            layer.confirm('添加模型的默认菜单是为了对指定模型进行单独的权限控制，添加菜单时如遇到同名的菜单将略过不处理，确定要添加？', function(index){
                $.ajax({
                    url: url,
                    success: function (result) {
                        if (result.code !== 0) {
                            layer.msg(result.msg, {shift: 6});
                            return false;
                        }
                        layer.msg(result.msg, {icon: 1}, function () {
                            if (result.reload) {
                                location.reload();
                            }
                            if (result.redirect) {
                                location.href = '{!! url()->previous() !!}';
                            }
                        });
                    }
                });
                layer.close(index);
            });
        }
    </script>
@endsection
