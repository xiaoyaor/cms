const { Field } = require('../../assets/libs/zanui/index');

var app = getApp();
Page(Object.assign({}, Field, {

  data: {
    base: {
      account: {
        name: 'account',
        focus: true,
        title: '账号',
        placeholder: '用户名/邮箱/手机号'
      },
      password: {
        name: 'password',
        title: '密码',
        inputType: 'password',
        placeholder: '请输入你的密码'
      },
    }
  },

  onLoad: function (options) {

  },

  onShow: function () {
    if (!app.globalData.userInfo) {
      app.error("请登录后再操作", function () {
        setTimeout(function () { wx.navigateBack({}) }, 2000);
      });
    }
  },

  formSubmit(event) {
    var that = this;
    app.request('/user/bind', event.detail.value, function (data, ret) {
      app.globalData.userInfo = data.userInfo;
      app.success(ret.msg, function () {
        setTimeout(function () {
          //要延时执行的代码
          wx.switchTab({
            url: 'index'
          });
        }, 2000); //延迟时间
      });
    }, function (data, ret) {
      app.error(ret.msg);
    });
  },

  formReset(event) {

  },
}))