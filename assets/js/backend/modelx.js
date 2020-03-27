define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cms/modelx/index',
                    add_url: 'cms/modelx/add',
                    edit_url: 'cms/modelx/edit',
                    del_url: 'cms/modelx/del',
                    multi_url: 'cms/modelx/multi',
                    table: 'model',
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
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name')},
                        {field: 'table', title: __('Table')},
                        {field: 'channeltpl', title: __('Channeltpl')},
                        {field: 'listtpl', title: __('Listtpl')},
                        {field: 'showtpl', title: __('Showtpl')},
                        {
                            field: 'createtime',
                            sortable: true,
                            visible: false,
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'updatetime',
                            sortable: true,
                            visible: false,
                            title: __('Updatetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'datalist', title: __('Operate'), table: table,
                            events: {
                                'click .btn-duplicate': function (e, value, row) {
                                    Layer.prompt({
                                        title: "请输入你需要新复制的模型表名",
                                        success: function (layero) {
                                            $("input", layero).prop("placeholder", "例如：cms_addontest，请不要加前缀");
                                        }
                                    }, function (value) {
                                        Fast.api.ajax({
                                            url: "cms/modelx/duplicate/ids/" + row.id,
                                            data: {table: value},
                                        }, function (data, ret) {
                                            Layer.closeAll();
                                            table.bootstrapTable('refresh');
                                            return false;
                                        });
                                    });
                                    return false;
                                }
                            },
                            buttons: [
                                {
                                    name: 'index',
                                    text: __('Main list'),
                                    classname: 'btn btn-xs btn-primary btn-addtabs',
                                    icon: 'fa fa-file',
                                    url: 'cms/archives/index?model_id={ids}'
                                },
                                {
                                    name: 'content',
                                    text: __('Addon list'),
                                    classname: 'btn btn-xs btn-success btn-addtabs',
                                    icon: 'fa fa-file',
                                    url: 'cms/archives/content/model_id/{ids}'
                                },
                                {
                                    name: 'fields',
                                    text: __('Fields'),
                                    classname: 'btn btn-xs btn-info btn-fields btn-addtabs',
                                    icon: 'fa fa-list',
                                    url: 'cms/fields/index/model_id/{ids}'
                                },
                                {
                                    name: 'duplicate',
                                    text: __('Duplicate'),
                                    classname: 'btn btn-xs btn-warning btn-duplicate',
                                    icon: 'fa fa-copy',
                                },
                            ],
                            formatter: Table.api.formatter.buttons
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            //获取标题拼音
            var si;
            $(document).on("keyup", "#c-name", function () {
                var value = $(this).val();
                if (value != '' && !value.match(/\n/)) {
                    clearTimeout(si);
                    si = setTimeout(function () {
                        Fast.api.ajax({
                            loading: false,
                            url: "cms/ajax/get_title_pinyin",
                            data: {title: value}
                        }, function (data, ret) {
                            $("#c-table").val("cms_addon" + data.pinyin);
                            return false;
                        }, function (data, ret) {
                            return false;
                        });
                    }, 200);
                }
            });
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