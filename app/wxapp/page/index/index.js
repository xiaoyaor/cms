const { Tab } = require('../../assets/libs/zanui/index');

var app = getApp();
Page(Object.assign({}, Tab, {
  data: {
    bannerList: [],
    archivesList: [],
    loading: false,
    nodata: false,
    nomore: false,
    tab: {
      list: [],
      selectedId: '0',
      scroll: true,
      height: 44
    },
  },
  channel: 0,
  page: 1,
  onLoad: function () {
    var that = this;
    this.channel = 0;
    this.page = 1;
    this.setData({ ["tab.list"]: app.globalData.indexTabList });
    app.request('/index/index', {}, function (data, ret) {
      that.setData({
        bannerList: data.bannerList,
        archivesList: data.archivesList,
        ["tab.list"]: data.tabList
      });
    }, function (data, ret) {
      app.error(ret.msg);
    });
  },
  onPullDownRefresh: function () {
    this.setData({ nodata: false, nomore: false });
    this.page = 1;
    this.loadArchives(function () {
      wx.stopPullDownRefresh();
    });
  },
  onReachBottom: function () {
    var that = this;
    this.loadArchives(function (data) {
      if (data.archivesList.length == 0) {
        app.info("暂无更多数据");
      }
    });
  },
  loadArchives: function (cb) {
    var that = this;
    if (that.data.nomore == true || that.data.loading == true) {
      return;
    }
    this.setData({ loading: true });
    app.request('/archives/index', { channel: this.channel, page: this.page }, function (data, ret) {
      that.setData({
        loading: false,
        nodata: that.page == 1 && data.archivesList.length == 0 ? true : false,
        nomore: that.page > 1 && data.archivesList.length == 0 ? true : false,
        archivesList: that.page > 1 ? that.data.archivesList.concat(data.archivesList) : data.archivesList,
      });
      that.page++;
      typeof cb == 'function' && cb(data);
    }, function (data, ret) {
      app.error(ret.msg);
    });
  },

  handleZanTabChange(e) {
    var componentId = e.componentId;
    var selectedId = e.selectedId;
    this.channel = selectedId;
    this.page = 1;
    this.setData({
      nodata: false,
      nomore: false,
      [`${componentId}.selectedId`]: selectedId
    });
    wx.pageScrollTo({ scrollTop: 0 });
    this.loadArchives();
  },
  onShareAppMessage: function () {
    return {
      title: 'FastAdmin',
      desc: '基于ThinkPHP5和Bootstrap的极速后台框架',
      path: '/page/index/index'
    }
  }
}))