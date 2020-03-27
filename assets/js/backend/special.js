define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cms/special/index' + location.search,
                    add_url: 'cms/special/add',
                    edit_url: 'cms/special/edit',
                    del_url: 'cms/special/del',
                    multi_url: 'cms/special/multi',
                    table: 'cms_special',
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
                        {field: 'title', title: __('Title')},
                        {field: 'flag', title: __('Flag'), searchList: {"hot": __('Hot'), "new": __('New'), "recommend": __('Recommend'), "top": __('Top')}, operate: 'FIND_IN_SET', formatter: Table.api.formatter.label},
                        {field: 'label', title: __('Label')},
                        {field: 'image', title: __('Image'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'banner', title: __('Banner'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'url', title: __('Url'), formatter:function (value, row, index) {
                                return '<div class=""><a href="' + row.url + '" class="btn btn-xs btn-default" target="_blank"><i class="fa fa-link"></i></a></div>';
                            }},
                        {field: 'views', title: __('Views')},
                        {field: 'comments', title: __('Comments')},
                        {field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), visible: false, operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"normal": __('Normal'), "hidden": __('Hidden')}, formatter: Table.api.formatter.status},
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

                $.validator.config({
                    rules: {
                        diyname: function (element) {
                            if (element.value.toString().match(/^\d+$/)) {
                                return __('Can not be digital');
                            }
                            return $.ajax({
                                url: 'cms/special/check_element_available',
                                type: 'POST',
                                data: {id: $("#special-id").val(), name: element.name, value: element.value},
                                dataType: 'json'
                            });
                        }
                    }
                });


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
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});