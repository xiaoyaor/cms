var app = getApp();
Page({

  data: {
    userInfo: null,
  },

  onLoad: function (options) {
    this.setData({ userInfo: app.globalData.userInfo });
  },

  onShow: function () {
    if (!app.globalData.userInfo) {
      app.error("请登录后再操作", function () {
        setTimeout(function () { wx.navigateBack({}) }, 2000);
      });
    }
  },

  formSubmit: function (event) {
    var that = this;
    if (event.detail.value.username == '') {
      app.error('用户名不能为空');
      return;
    }
    if (event.detail.value.nickname == '') {
      app.error('昵称不能为空');
      return;
    }
    app.request('/user/profile', event.detail.value, function (data) {
      that.setData({ userInfo: data.userInfo });
      app.globalData.userInfo = data.userInfo;
      app.success('修改成功!', function () {
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
  //上传头像
  uploadAvatar: function () {
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
              app.success('头像上传成功');
              that.setData({ ["userInfo.avatar"]: app.globalData.uploadConfig.cdnurl + data.url });
            }
          },
          error: function (e) {
            console.log(e);
          }
        });
      }, error: function () {
        console.log(e);
      }
    });
  }

})