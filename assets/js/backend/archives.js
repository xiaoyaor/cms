define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template'], function ($, undefined, Backend, Table, Form, Template) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cms/archives/index',
                    add_url: 'cms/archives/add',
                    edit_url: 'cms/archives/edit',
                    del_url: 'cms/archives/del',
                    multi_url: 'cms/archives/multi',
                    dragsort_url: '',
                    table: 'archives',
                }
            });

            var table = $("#table");

            //在表格内容渲染完成后回调的事件
            table.on('post-body.bs.table', function (e, settings, json, xhr) {
                //当为新选项卡中打开时
                if (Config.cms.archiveseditmode == 'addtabs') {
                    $(".btn-editone", this)
                        .off("click")
                        .removeClass("btn-editone")
                        .addClass("btn-addtabs")
                        .prop("title", __('Edit'));
                }
            });
            //当双击单元格时
            table.on('dbl-click-row.bs.table', function (e, row, element, field) {
                $(".btn-addtabs", element).trigger("click");
            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh DESC,id DESC',
                searchFormVisible: Fast.api.query("model_id") ? true : false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), sortable: true},
                        {
                            field: 'user_id',
                            title: __('User_id'),
                            visible: false,
                            addclass: 'selectpage',
                            extend: 'data-source="user/user/index" data-field="nickname"',
                            operate: '=',
                            formatter: Table.api.formatter.search
                        },
                        {
                            field: 'channel_id',
                            title: __('Channel_id'),
                            visible: false,
                            operate: 'in',
                            formatter: Table.api.formatter.search
                        },
                        {
                            field: 'channel.name',
                            title: __('Channel'),
                            operate: false,
                            formatter: function (value, row, index) {
                                return '<a href="javascript:;" class="searchit" data-field="channel_id" data-value="' + row.channel_id + '">' + value + '</a>';
                            }
                        },
                        {
                            field: 'model_id', title: __('Model'), visible: false, align: 'left', addclass: "selectpage", extend: "data-source='cms/modelx/index' data-field='name'"
                        },
                        {
                            field: 'title', title: __('Title'), align: 'left', formatter: function (value, row, index) {
                                return '<div class=""><a href="' + row.url + '" target="_blank"><span style="color:' + (row.style_color ? row.style_color : 'inherit') + ';font-weight:' + (row.style_bold ? 'bold' : 'normal') + '">' + value + '</span></a></div>' + Table.api.formatter.flag.call(this, row['flag'], row, index);
                            }
                        },
                        {field: 'flag', title: __('Flag'), operate: '=', visible: false, searchList: Config.flagList, formatter: Table.api.formatter.flag},
                        {field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'views', title: __('Views'), operate: 'BETWEEN', sortable: true},
                        {field: 'comments', title: __('Comments'), operate: 'BETWEEN', sortable: true},
                        {field: 'weigh', title: __('Weigh'), operate: false, sortable: true},
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            visible: false,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'updatetime',
                            title: __('Updatetime'),
                            visible: false,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'publishtime',
                            title: __('Publishtime'),
                            sortable: true,
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {field: 'status', title: __('Status'), searchList: {"normal": __('Status normal'), "hidden": __('Status hidden'), "rejected": __('Status rejected'), "pulloff": __('Status pulloff')}, formatter: Table.api.formatter.status},
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

            var url = '';
            //当为新选项卡中打开时
            if (Config.cms.archiveseditmode == 'addtabs') {
                url = (url + '?ids=' + $(".commonsearch-table input[name=channel_id]").val());
            }
            $(".btn-add").off("click").on("click", function () {
                var url = 'cms/archives/add?channel=' + $(".commonsearch-table input[name=channel_id]").val();
                //当为新选项卡中打开时
                if (Config.cms.archiveseditmode == 'addtabs') {
                    Fast.api.addtabs(url, __('Add'));
                } else {
                    Fast.api.open(url, __('Add'), $(this).data() || {});
                }
                return false;
            });

            $(document).on("click", "a.btn-channel", function () {
                $("#archivespanel").toggleClass("col-md-9", $("#channelbar").hasClass("hidden"));
                $("#channelbar").toggleClass("hidden");
            });

            require(['jstree'], function () {
                //全选和展开
                $(document).on("click", "#checkall", function () {
                    $("#channeltree").jstree($(this).prop("checked") ? "check_all" : "uncheck_all");
                });
                $(document).on("click", "#expandall", function () {
                    $("#channeltree").jstree($(this).prop("checked") ? "open_all" : "close_all");
                });
                $('#channeltree').on("changed.jstree", function (e, data) {
                    $(".commonsearch-table input[name=channel_id]").val(data.selected.join(","));
                    table.bootstrapTable('refresh', {});
                    return false;
                });
                $('#channeltree').jstree({
                    "themes": {
                        "stripes": true
                    },
                    "checkbox": {
                        "keep_selected_style": false,
                    },
                    "types": {
                        "channel": {
                            "icon": "fa fa-th",
                        },
                        "list": {
                            "icon": "fa fa-list",
                        },
                        "link": {
                            "icon": "fa fa-link",
                        },
                        "disabled": {
                            "check_node": false,
                            "uncheck_node": false
                        }
                    },
                    'plugins': ["types", "checkbox"],
                    "core": {
                        "multiple": true,
                        'check_callback': true,
                        "data": Config.channelList
                    }
                });
            });

            $(document).on('click', '.btn-move', function () {
                var ids = Table.api.selectedids(table);
                Layer.open({
                    title: __('Move'),
                    content: Template("channeltpl", {}),
                    btn: [__('Move')],
                    yes: function (index, layero) {
                        var channel_id = $("select[name='channel']", layero).val();
                        if (channel_id == 0) {
                            Toastr.error(__('Please select channel'));
                            return;
                        }
                        Fast.api.ajax({
                            url: "cms/archives/move/ids/" + ids.join(","),
                            type: "post",
                            data: {channel_id: channel_id},
                        }, function () {
                            table.bootstrapTable('refresh', {});
                            Layer.close(index);
                        });
                    },
                    success: function (layero, index) {
                    }
                });
            });
        },
        content: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cms/archives/content/model_id/' + Config.model_id,
                    add_url: '',
                    edit_url: 'cms/archives/edit',
                    del_url: 'cms/archives/del',
                    multi_url: '',
                    table: '',
                }
            });

            var table = $("#table");
            //在表格内容渲染完成后回调的事件
            table.on('post-body.bs.table', function (e, settings, json, xhr) {
                $(".btn-editone", this)
                    .off("click")
                    .removeClass("btn-editone")
                    .addClass("btn-addtabs")
                    .prop("title", __('Edit'));
            });
            //默认字段
            var columns = [
                {checkbox: true},
                //这里因为涉及到关联多个表,因为用了两个字段来操作,一个隐藏,一个搜索
                {field: 'main.id', title: __('Id'), visible: false},
                {field: 'id', title: __('Id'), operate: false},
                {field: 'title', title: __('Title'), operate: 'like'},
                {
                    field: 'channel_id',
                    title: __('Channel_id'),
                    addclass: 'selectpage',
                    extend: 'data-source="cms/channel/index"',
                    formatter: Table.api.formatter.search
                },
                {field: 'channel_name', title: __('Channel_name'), operate: false}
            ];
            //动态追加字段
            $.each(Config.fields, function (i, j) {
                var data = {field: j.field, title: j.title, operate: 'like'};
                //如果是图片,加上formatter
                if (j.type == 'image') {
                    data.events = Table.api.events.image;
                    data.formatter = Table.api.formatter.image;
                } else if (j.type == 'images') {
                    data.events = Table.api.events.image;
                    data.formatter = Table.api.formatter.images;
                } else if (j.type == 'radio' || j.type == 'checkbox' || j.type == 'select' || j.type == 'selects') {
                    data.formatter = Controller.api.formatter.content;
                    data.extend = j.content;
                    data.searchList = j.content;
                }
                columns.push(data);
            });
            //追加操作字段
            columns.push({
                field: 'operate',
                title: __('Operate'),
                table: table,
                events: Table.api.events.operate,
                formatter: Table.api.formatter.operate
            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: columns
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        diyform: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cms/archives/diyform/diyform_id/' + Config.diyform_id,
                    add_url: '',
                    edit_url: 'cms/archives/edit',
                    del_url: 'cms/archives/del',
                    multi_url: '',
                    table: '',
                }
            });

            var table = $("#table");
            //在表格内容渲染完成后回调的事件
            table.on('post-body.bs.table', function (e, settings, json, xhr) {
                $(".btn-editone", this)
                    .off("click")
                    .removeClass("btn-editone")
                    .addClass("btn-addtabs")
                    .prop("title", __('Edit'));
            });
            //默认字段
            var columns = [
                {checkbox: true},
                {field: 'id', title: __('Id'), operate: false},
            ];
            //动态追加字段
            $.each(Config.fields, function (i, j) {
                var data = {field: j.field, title: j.title, operate: 'like'};
                //如果是图片,加上formatter
                if (j.type == 'image') {
                    data.formatter = Table.api.formatter.image;
                } else if (j.type == 'images') {
                    data.formatter = Table.api.formatter.images;
                } else if (j.type == 'radio' || j.type == 'check' || j.type == 'select' || j.type == 'selects') {
                    data.formatter = Controller.api.formatter.content;
                    data.extend = j.content;
                }
                columns.push(data);
            });
            //追加操作字段
            columns.push({
                field: 'operate',
                title: __('Operate'),
                table: table,
                events: Table.api.events.operate,
                formatter: Table.api.formatter.operate
            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: columns
            })
            ;

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'cms/archives/recyclebin',
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'title', title: __('Title'), align: 'left'},
                        {field: 'image', title: __('Image'), operate: false, formatter: Table.api.formatter.image},
                        {
                            field: 'deletetime',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '130px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'cms/archives/restore'
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'cms/archives/destroy'
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            $(document).on('click', '.btn-destroyall', function () {
                var that = this;
                Layer.confirm(__('Are you sure you want to truncate?'), function () {
                    Fast.api.ajax($(that).attr("href"), function () {
                        Layer.closeAll();
                        table.bootstrapTable('refresh');
                    }, function () {
                        Layer.closeAll();
                    });
                });
                return false;
            });
            $(document).on('click', '.btn-restoreall,.btn-restoreit,.btn-destroyit', function () {
                Fast.api.ajax($(this).attr("href"), function () {
                    table.bootstrapTable('refresh');
                });
                return false;
            });
        },
        add: function () {
            var last_channel_id = localStorage.getItem('last_channel_id');
            var channel = Fast.api.query("channel");
            if (channel) {
                var channelIds = channel.split(",");
                $(channelIds).each(function (i, j) {
                    if ($("#c-channel_id option[value='" + j + "']:disabled").size() > 0) {
                        return true;
                    }
                    last_channel_id = j;
                    return false;
                });
            }
            if (last_channel_id) {
                $("#c-channel_id option[value='" + last_channel_id + "']").prop("selected", true);
            }
            Controller.api.bindevent();
            $("#c-channel_id").trigger("change");
        },
        edit: function () {
            Controller.api.bindevent();
            $("#c-channel_id").trigger("change");
        },
        api: {
            formatter: {
                content: function (value, row, index) {
                    var extend = this.extend;
                    if (!value) {
                        return '';
                    }
                    var valueArr = value.toString().split(/,/);
                    var result = [];
                    $.each(valueArr, function (i, j) {
                        result.push(typeof extend[j] !== 'undefined' ? extend[j] : j);
                    });
                    return result.join(',');
                }
            },
            bindevent: function () {
                var refreshStyle = function () {
                    var style = [];
                    if ($(".btn-bold").hasClass("active")) {
                        style.push("b");
                    }
                    if ($(".btn-color").hasClass("active")) {
                        style.push($(".btn-color").data("color"));
                    }
                    $("input[name='row[style]']").val(style.join("|"));
                };
                require(['jquery-colorpicker'], function () {
                    $('.colorpicker').colorpicker({
                        color: function () {
                            var color = "#000000";
                            var rgb = $("#c-title").css('color').match(/^rgb\(((\d+),\s*(\d+),\s*(\d+))\)$/);
                            if (rgb) {
                                color = rgb[1];
                            }
                            return color;
                        }
                    }, function (event, obj) {
                        $("#c-title").css('color', '#' + obj.hex);
                        $(event).addClass("active").data("color", '#' + obj.hex);
                        refreshStyle();
                    }, function (event) {
                        $("#c-title").css('color', 'inherit');
                        $(event).removeClass("active");
                        refreshStyle();
                    });
                });
                require(['jquery-tagsinput'], function () {
                    //标签输入
                    var elem = "#c-tags";
                    var tags = $(elem);
                    tags.tagsInput({
                        width: 'auto',
                        defaultText: '输入后空格确认',
                        minInputWidth: 110,
                        height: '36px',
                        placeholderColor: '#999',
                        onChange: function (row) {
                            if (typeof callback === 'function') {

                            } else {
                                $(elem + "_addTag").focus();
                                $(elem + "_tag").trigger("blur.autocomplete").focus();
                            }
                        },
                        autocomplete: {
                            url: 'cms/tags/autocomplete',
                            minChars: 1,
                            menuClass: 'autocomplete-tags'
                        }
                    });
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
                                data: {title: value, delimiter: "-"}
                            }, function (data, ret) {
                                $("#c-diyname").val(data.pinyin);
                                return false;
                            }, function (data, ret) {
                                return false;
                            });
                        }, 200);
                    }
                });
                $(document).on('click', '.btn-bold', function () {
                    $("#c-title").toggleClass("text-bold", !$(this).hasClass("active"));
                    $(this).toggleClass("text-bold active");
                    refreshStyle();
                });
                $(document).on('change', '#c-channel_id', function () {
                    var model = $("option:selected", this).attr("model");
                    Fast.api.ajax({
                        url: 'cms/archives/get_channel_fields',
                        data: {channel_id: $(this).val(), archives_id: $("#archive-id").val()}
                    }, function (data) {
                        if ($("#extend").data("model") != model) {
                            $("#extend").html(data.html).data("model", model);
                            Form.api.bindevent($("#extend"));
                        }
                        return false;
                    });
                    localStorage.setItem('last_channel_id', $(this).val());
                });
                $(document).on("fa.event.appendfieldlist", ".downloadlist", function (a) {
                    Form.events.plupload(this);
                    $(".fachoose", this).off("click");
                    Form.events.faselect(this);
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
                        data: {title: $("#c-title").val(), tags: $("#c-tags").val(), content: $("#c-content").val()}
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
                                url: 'cms/archives/check_element_available',
                                type: 'POST',
                                data: {id: $("#archive-id").val(), name: element.name, value: element.value},
                                dataType: 'json'
                            });
                        },
                        isnormal: function (element) {
                            return $("#c-status").val() == 'normal' ? true : false;
                        }
                    }
                });
                var iscontinue = false;
                $(document).on("click", ".btn-continue", function () {
                    iscontinue = true;
                    $(this).prev().trigger("click");
                });
                Form.api.bindevent($("form[role=form]"), function () {
                    if (iscontinue) {
                        $(window).scrollTop(0);
                        location.reload();
                        top.window.Toastr.success(__('Operation completed'));
                        return false;
                    } else {
                        if (Config.cms.archiveseditmode == 'addtabs') {
                            var obj = top.window.$("ul.nav-addtabs li.active");
                            top.window.Toastr.success(__('Operation completed'));
                            top.window.$(".sidebar-menu a[url$='/cms/archives'][addtabs]").click();
                            top.window.$(".sidebar-menu a[url$='/cms/archives'][addtabs]").dblclick();
                            obj.find(".fa-remove").trigger("click");
                        }
                    }
                });
            }
        }
    };
    return Controller;
});