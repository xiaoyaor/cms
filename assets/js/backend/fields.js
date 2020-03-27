define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            var params = Config.model_id ? '/model_id/' + Config.model_id : '/diyform_id/' + Config.diyform_id;
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cms/fields/index' + params,
                    add_url: 'cms/fields/add' + params,
                    edit_url: 'cms/fields/edit' + params,
                    del_url: 'cms/fields/del' + params,
                    multi_url: 'cms/fields/multi' + params,
                    table: 'cms_fields',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                pagination: false,
                search: false,
                commonSearch: false,
                columns: [
                    [
                        {
                            field: 'state', checkbox: true, formatter: function (value, row, index) {
                                if (row.state === false) {
                                    return {
                                        disabled: true,
                                    }
                                } else {
                                    return {
                                        disabled: false,
                                    }
                                }
                            }
                        },
                        {field: 'id', sortable: true, title: __('Id')},
                        {field: 'model_id', visible: false, operate: false, title: __('Model_id')},
                        {field: 'diyform_id', visible: false, operate: false, title: __('Diyform_id')},
                        {
                            field: 'name', title: __('Name'), formatter: function (value, row, index) {
                                return row.issystem ? "<span class='text-muted'>" + value + "</span>" : value;
                            }
                        },
                        {
                            field: 'type', title: __('Type'), formatter: function (value, row, index) {
                                return row.issystem ? "<span class='text-muted'>" + value + "</span>" : value;
                            }
                        },
                        {
                            field: 'title', title: __('Title'), formatter: function (value, row, index) {
                                return row.issystem ? "<span class='text-muted'>" + value + "</span>" : value;
                            }
                        },
                        {
                            field: 'isfilter', title: __('Isfilter'), searchList: {"1": __('Yes'), "0": __('No')}, formatter: function (value, row, index) {
                                return row.issystem ? "-" : Table.api.formatter.toggle.call(this, value, row, index);
                            }
                        },
                        {
                            field: 'iscontribute', title: __('Iscontribute'), searchList: {"1": __('Yes'), "0": __('No')}, formatter: function (value, row, index) {
                                return row.issystem ? "-" : Table.api.formatter.toggle.call(this, value, row, index);
                            }
                        },
                        {field: 'weigh', title: __('Weigh'), visible: false},
                        {field: 'createtime', title: __('Createtime'), visible: false, operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), visible: false, operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {
                            field: 'status', title: __('Status'), formatter: function (value, row, index) {
                                return row.issystem ? "-" : Table.api.formatter.status.call(this, value, row, index);
                            }
                        },
                        {
                            field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            formatter: function (value, row, index) {
                                return row.issystem ? "<div style='height:26px;line-height:26px;'>-</div>" : Table.api.formatter.operate.call(this, value, row, index);
                            }
                        }
                    ]
                ],
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
                //不可见的元素不验证
                $("form#add-form").data("validator-options", {ignore: ':hidden'});
                $(document).on("change", "#c-type", function () {
                    $(".tf").addClass("hidden");
                    $(".tf.tf-" + $(this).val()).removeClass("hidden");

                });
                Form.api.bindevent($("form[role=form]"));
                $("#c-type").trigger("change");
            }
        }
    };
    return Controller;
});