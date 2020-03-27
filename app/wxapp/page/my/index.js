var app = getApp();
Page({
  data: {
    isWxapp: true,
    userInfo: {
      id: 0,
      avatar: '/assets/images/avatar.png',
      nickname: '游客',
      balance: 0,
      score: 0,
      level: 0
    }
  },
  onLoad: function () {
    var that = this;
  },
  onShow: function () {
    var that = this;
    if (app.globalData.userInfo) {
      that.setData({ userInfo: app.globalData.userInfo, isWxapp: that.isWxapp() });
    }
  },
  login: function () {
    var that = this;
    app.login(function () {
      that.setData({ userInfo: app.globalData.userInfo, isWxapp: that.isWxapp() });
    });
  },
  isWxapp: function () {
    return app.globalData.userInfo ? app.globalData.userInfo.username.match(/^u\d+$/) : true;
  },
  showTips: function (event) {
    var tips = {
      balance: '余额通过插件的出售获得',
      score: '积分可以通过回答问题获得',
      level: '等级通过官网活跃进行升级',
    };
    var type = event.currentTarget.dataset.type;
    var content = tips[type];
    wx.showModal({
      title: '温馨提示',
      content: content,
      showCancel: false
    });
  },
  //点击头像上传
  uploadAvatar: function () {
    if (!app.globalData.userInfo) {
      app.error("请登录后再操作");
      return false;
    }
    var that = this;
    wx.chooseImage({
      success: function (res) {
        var tempFilePaths = res.tempFilePaths;
        var formData = app.globalData.config.upload.multipart;
        formData.token = app.globalData.userInfo.token;
        wx.uploadFile({
          url: app.globalData.config.upload.uploadurl,
          filePath: tempFilePaths[0],
          name: 'file',
          formData: formData,
          success: function (res) {
            var data = JSON.parse(res.data);
            if (data.code == 200) {
              app.request("/user/avatar", { avatar: data.url }, function (data, ret) {
                app.success('头像上传成功!');
                app.globalData.userInfo = data.userInfo;
                that.setData({ userInfo: data.userInfo, isWxapp: that.isWxapp()});
              }, function (data, ret) {
                app.error(ret.msg);
              });
            }
          }, error: function (res) {
            app.error("上传头像失败!");
          }
        });
      }, error: function (res) {
        app.error("上传头像失败!");
      }
    });
  }
})
