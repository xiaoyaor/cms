define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cms/diyform/index',
                    add_url: 'cms/diyform/add',
                    edit_url: 'cms/diyform/edit',
                    del_url: 'cms/diyform/del',
                    multi_url: 'cms/diyform/multi',
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
                        {field: 'title', title: __('Title')},
                        {field: 'table', title: __('Table')},
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
                            field: 'url', title: __('Url'), operate: false, formatter: function (value, row, index) {
                                return '<a href="' + value + '" target="_blank" class="btn btn-default btn-xs"><i class="fa fa-link"></i></a>';
                            }
                        },
                        {
                            field: 'datalist', title: __('Operate'), table: table,
                            buttons: [
                                {
                                    name: 'content',
                                    text: __('数据列表'),
                                    classname: 'btn btn-xs btn-success btn-addtabs',
                                    icon: 'fa fa-file',
                                    url: 'cms/diydata/index/diyform_id/{ids}'
                                },
                                {
                                    name: 'fields',
                                    text: __('字段列表'),
                                    classname: 'btn btn-xs btn-info btn-fields btn-addtabs',
                                    icon: 'fa fa-list',
                                    url: 'cms/fields/index/diyform_id/{ids}'
                                },
                            ],
                            formatter: Table.api.formatter.buttons
                        },
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.status},
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
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                $.validator.config({
                    rules: {
                        diyname: function (element) {
                            if (element.value.toString().match(/^\d+$/)) {
                                return __('Can not be digital');
                            }
                            return $.ajax({
                                url: 'cms/diyform/check_element_available',
                                type: 'POST',
                                data: {id: $("#diyform-id").val(), name: element.name, value: element.value},
                                dataType: 'json'
                            });
                        }
                    }
                });

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
                                $("#c-table").val("cms_diyform_" + data.pinyin);
                                $("#c-diyname").val(data.pinyin);
                                return false;
                            }, function (data, ret) {
                                return false;
                            });
                        }, 200);
                    }
                });
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});