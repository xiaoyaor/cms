var app = getApp();
Page({

  data: {
    userInfo: null,
    archivesInfo: {
      article: {}
    },
    commentList: [],
    loading: false,
    nodata: false,
    nomore: false,
    form: {
      quotepid: 0,
      message: '',
      focus: false
    }
  },
  page: 1,

  onLoad: function(options) {
    var that = this;
    that.setData({
      userInfo: app.globalData.userInfo
    });
    app.request('/archives/detail', {
      id: options.id
    }, function(data, ret) {
      var content = data.archivesInfo.content;
      data.archivesInfo.article = app.towxml.toJson(content, 'html');
      data.commentList.forEach(function(item) {
        item.article = app.towxml.toJson(item.content, 'html');
      });
      that.setData({
        archivesInfo: data.archivesInfo,
        commentList: data.commentList
      });
      that.page++;
    }, function(data, ret) {
      app.error(ret.msg);
    });
  },
  reply: function(event) {
    var that = this;
    var pid = event.currentTarget.dataset.pid;
    var nickname = event.currentTarget.dataset.nickname;
    that.setData({
      form: {
        quotepid: pid,
        message: '@' + nickname + ' ',
        focus: true
      }
    });
  },
  login: function(event) {
    var that = this;
    app.login(function(data) {
      that.setData({
        userInfo: app.globalData.userInfo
      });
      that.onLoad({
        id: that.data.archivesInfo.id
      });
    });
  },
  formSubmit: function(event) {
    var that = this;
    var pid = event.currentTarget.dataset.pid;
    if (!app.globalData.userInfo) {
      app.error('请登录后再评论');
      return;
    }
    if (event.detail.value.message == '') {
      app.error('内容不能为空');
      return;
    }
    app.request('/comment/post', {
      aid: this.data.archivesInfo.id,
      pid: this.data.form.quotepid,
      username: event.detail.value.username,
      content: event.detail.value.content
    }, function(data) {
      app.success('发表成功!');
      that.setData({
        form: {
          quotepid: 0,
          message: '',
          focus: false
        },
        commentList: [],
        nodata: false,
        nomore: false
      });
      if (that.data.commentList.length < 10) {
        that.page = 1;
      } else {
        that.data.commentList = that.data.commentList.slice(0, 10);
        that.page = 2;
      }
      that.onReachBottom();
    }, function(data, ret) {
      app.error(ret.msg);
    });
  },
  vote: function(event) {
    var that = this;
    app.vote(event, function(data) {
      that.setData({
        ['archivesInfo.likes']: data.likes,
        ['archivesInfo.dislikes']: data.dislikes,
        ['archivesInfo.likeratio']: data.likeratio
      });
    });
  },
  onReachBottom: function() {
    var that = this;
    this.loadComment(function(data) {
      if (data.commentList.length == 0) {
        //app.info("暂无更多数据");
      }
    });
  },
  loadComment: function(cb) {
    var that = this;
    if (that.data.nomore == true || that.data.loading == true) {
      return;
    }
    this.setData({
      loading: true
    });
    app.request('/comment', {
      aid: this.data.archivesInfo.id,
      page: this.page
    }, function(data, ret) {
      data.commentList.forEach(function(item) {
        item.article = app.towxml.toJson(item.content, 'html');
      });
      that.setData({
        loading: false,
        nodata: that.page == 1 && data.commentList.length == 0 ? true : false,
        nomore: that.page > 1 && data.commentList.length == 0 ? true : false,
        commentList: that.page > 1 ? that.data.commentList.concat(data.commentList) : data.commentList,
      });
      that.page++;
      typeof cb == 'function' && cb(data);
    }, function(data, ret) {
      that.setData({
        loading: false
      });
      app.error(ret.msg);
    });
  },

  pay: function() {
    var that = this;
    wx.showModal({
      title: '温馨提示',
      content: '确认支付' + this.data.archivesInfo.price + '元查看付费内容？',
      confirmText: "确认支付",
      cancelText: "暂不支付",
      success: function(res) {
        if (res.confirm) {
          if (that.data.loading == true) {
            return;
          }
          that.setData({
            loading: true
          });
          app.request('/archives/order', {
            id: that.data.archivesInfo.id
          }, function(data, ret) {
            if (data == null) {
              //余额支付或已支付过
              that.onLoad();
            } else {
              //发起支付
              wx.requestPayment({
                'timeStamp': data.timeStamp,
                'nonceStr': data.nonceStr,
                'package': data.package,
                'signType': data.signType,
                'paySign': data.paySign,
                'success': function(res) {
                  if (res.errMsg == 'requestPayment:ok') {
                    that.onLoad({
                      id: that.data.archivesInfo.id
                    });
                  } else {
                    app.error("支付失败请重试");
                  }
                },
                'fail': function(res) {
                  console.log(res);
                  app.error("支付失败请重试");
                },
                'complete': function(res) {}
              });
            }
          }, function(data, ret) {
            that.setData({
              loading: false
            });
            app.error(ret.msg);
          });
        } else {
          console.log('用户暂不支付');
        }
      }
    });
  },
  share: function() {
    wx.showShareMenu({});
  },
  onShareAppMessage: function() {
    return {
      title: this.data.archivesInfo.title,
      desc: this.data.archivesInfo.intro,
      path: '/page/news/detail?id=' + this.data.archivesInfo.id
    }
  },
})