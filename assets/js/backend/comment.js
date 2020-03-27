define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cms/comment/index',
                    add_url: 'cms/comment/add',
                    edit_url: 'cms/comment/edit',
                    del_url: 'cms/comment/del',
                    multi_url: 'cms/comment/multi',
                    table: 'comment',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', sortable: true, title: __('Id')},
                        {field: 'type', title: __('Type'), formatter: Table.api.formatter.flag, custom: {archives: 'success', page: 'info'}, searchList: Config.typeList},
                        {field: 'aid', sortable: true, title: __('Aid'), formatter: Table.api.formatter.search},
                        {field: 'pid', sortable: true, title: __('Pid'), formatter: Table.api.formatter.search, visible: false},
                        {field: 'user_id', sortable: true, title: __('User_id'), formatter: Table.api.formatter.search},
                        {field: 'user.nickname', operate: false, title: __('Nickname')},
                        {
                            field: 'title', title: __('Title'), operate: false, formatter: function (value, row, index) {
                                return row.spage && row.spage.id ? row.spage.title : (row.archives && row.archives.id ? row.archives.title : __('None'));
                            }
                        },
                        {field: 'content', sortable: false, title: __('Content')},
                        {field: 'comments', sortable: true, title: __('Comments'), visible: false},
                        {field: 'ip', title: __('Ip'), formatter: Table.api.formatter.search},
                        {field: 'useragent', title: __('Useragent'), visible: false},
                        {field: 'subscribe', sortable: true, title: __('Subscribe'), visible: false},
                        {field: 'createtime', sortable: true, title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', sortable: true, visible: false, title: __('Updatetime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime, visible: false},
                        {field: 'status', title: __('Status'), searchList: {"normal": __('normal'), "hidden": __('hidden')}, formatter: Table.api.formatter.status},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});