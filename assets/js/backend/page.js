define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cms/page/index',
                    add_url: 'cms/page/add',
                    edit_url: 'cms/page/edit',
                    del_url: 'cms/page/del',
                    multi_url: 'cms/page/multi',
                    table: 'page',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', sortable: true, title: __('Id')},
                        {field: 'type', title: __('Type'), formatter: Table.api.formatter.search, searchList: Config.typeList},
                        {field: 'title', title: __('Title')},
                        {field: 'flag', title: __('Flag'), formatter: Table.api.formatter.flag},
                        {field: 'image', title: __('Image'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'views', sortable: true, title: __('Views'), operate: 'BETWEEN'},
                        {field: 'comments', sortable: true, title: __('Comments'), operate: 'BETWEEN'},
                        {
                            field: 'url', title: __('Url'), formatter: function (value, row, index) {
                                return '<a href="' + value + '" target="_blank" class="btn btn-default btn-xs"><i class="fa fa-link"></i></a>';
                            }
                        },
                        {field: 'createtime', sortable: true, title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', sortable: true, visible: false, title: __('Updatetime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'weigh', sortable: true, title: __('Weigh')},
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.status},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        select: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cms/page/select',
                    add_url: 'cms/page/add',
                    edit_url: 'cms/page/edit',
                    table: 'page',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', sortable: true, title: __('Id')},
                        {field: 'title', title: __('Title')},
                        {field: 'image', title: __('Image'), formatter: Table.api.formatter.image},
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.status},
                        {
                            field: 'select', title: __('Operate'), table: table, formatter: Table.api.formatter.buttons,
                            events: {
                                'click .btn-select-one': function (e, value, row) {
                                    Fast.api.close(row);
                                }
                            },
                            buttons: [
                                {
                                    name: "select",
                                    text: __("Select"),
                                    classname: "btn btn-xs btn-success btn-select-one"
                                }
                            ]
                        },
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
                //获取标题拼音
                var si;
                $(document).on("keyup", "#c-title", function () {
                    var value = $(this).val();
                    if (value != '' && !value.match(/\n/)) {
                        clearTimeout(si);
                        si = setTimeout(function () {
                            Fast.api.ajax({
                                loading: false,
                                url: "cms/ajax/get_title_pinyin",
                                data: {title: value}
                            }, function (data, ret) {
                                $("#c-diyname").val(data.pinyin);
                                return false;
                            }, function (data, ret) {
                                return false;
                            });
                        }, 200);
                    }
                });
                $(document).on("click", ".btn-legal", function (a) {
                    Fast.api.ajax({
                        url: "cms/ajax/check_content_islegal",
                        data: {content: $("#c-content").val()}
                    }, function (data, ret) {

                    }, function (data, ret) {
                        if ($.isArray(data)) {
                            Layer.alert(__('Banned words') + "：" + data.join(","));
                        }
                    });
                });
                $(document).on("click", ".btn-keywords", function (a) {
                    Fast.api.ajax({
                        url: "cms/ajax/get_content_keywords",
                        data: {title: $("#c-title").val(), content: $("#c-content").val()}
                    }, function (data, ret) {
                        $("#c-keywords").val(data.keywords);
                        $("#c-description").val(data.description);
                    });
                });

                $.validator.config({
                    rules: {
                        diyname: function (element) {
                            if (element.value.toString().match(/^\d+$/)) {
                                return __('Can not be digital');
                            }
                            return $.ajax({
                                url: 'cms/page/check_element_available',
                                type: 'POST',
                                data: {id: $("#page-id").val(), name: element.name, value: element.value},
                                dataType: 'json'
                            });
                        }
                    }
                });
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});