var app = getApp();
Page({
  data: {
    commentList: [],
    loading: false,
    nodata: false,
    nomore: false,
  },
  page: 1,
  onLoad: function () {
    this.page = 1;
    this.loadComment();
  },

  onShow: function () {
    if (!app.globalData.userInfo) {
      app.error("请登录后再操作", function () {
        setTimeout(function () { wx.navigateBack({}) }, 2000);
      });
    }
  },
  
  onPullDownRefresh: function () {
    this.setData({ nodata: false, nomore: false });
    this.page = 1;
    this.loadComment();
  },
  onReachBottom: function () {
    var that = this;
    this.loadComment(function (data) {
      if (data.commentList.length == 0) {
        app.info("暂无更多数据");
      }
    });
  },
  loadComment: function (cb) {
    var that = this;
    if (that.data.nomore == true || that.data.loading == true) {
      return;
    }
    this.setData({ loading: true });
    app.request('/my/comment', { page: this.page }, function (data, ret) {
      that.setData({
        loading: false,
        nodata: that.page == 1 && data.commentList.length == 0 ? true : false,
        nomore: that.page > 1 && data.commentList.length == 0 ? true : false,
        commentList: that.page > 1 ? that.data.commentList.concat(data.commentList) : data.commentList,
      });
      that.page++;
      typeof cb == 'function' && cb(data);
    }, function (data, ret) {
      that.setData({
        loading: false
      });
      app.error(ret.msg);
    });
  },
})