var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    type: 'getuserinfo'
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({ type: options.type });
  },

  bindGetUserInfo: function (e) {
    console.log(e.detail);
    if (e.detail.errMsg != 'getUserInfo:ok') {
      wx.showModal({
        title: '温馨提示',
        content: '你拒绝了授权登录,为了更好的为你提供服务,请重新进行登录',
      })
    } else {
      app.login(function () {
        wx.navigateBack({

        });
      });
    }
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})