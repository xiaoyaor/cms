define(['jquery', 'bootstrap', 'frontend', 'template', 'form'], function ($, undefined, Frontend, Template, Form) {
    var Controller = {
        my: function () {

        },
        post: function () {

            require(['jquery-tagsinput'], function () {
                //标签输入
                var elem = "#c-tags";
                var tags = $(elem);
                tags.tagsInput({
                    width: 'auto',
                    defaultText: '输入后回车确认',
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
                        url: 'cms.archives/tags_autocomplete',
                        minChars: 1,
                        menuClass: 'autocomplete-tags'
                    }
                });
            });
            $(document).on('change', '#c-channel_id', function () {
                var model = $("option:selected", this).attr("model");
                Fast.api.ajax({
                    url: 'cms.archives/get_channel_fields',
                    data: {channel_id: $(this).val(), archives_id: Config.archives_id}
                }, function (data) {
                    if ($("#extend").data("model") != model) {
                        $("#extend").html(data.html).data("model", model);
                        Form.api.bindevent($("#extend"));
                    }
                    return false;
                });
                localStorage.setItem('last_channel_id', $(this).val());
            });
            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                setTimeout(function () {
                    location.href = Fast.api.fixurl('cms.archives/my');
                }, 1500);
            });
            $("#c-channel_id").trigger("change");
        }
    };
    return Controller;
});