// pages/info/info.js
var app = getApp();
Page({
  data: {
    auth: 0
  },
  onLoad: function (options) {
    // 页面初始化 options为页面跳转所带来的参数
  },
  onReady: function () {
    // 页面渲染完成
  },
  onShow: function () {
    // 页面显示
    var auth = app.globalData.auth;
    this.setData({auth: auth});
    if (auth == 1) {
      wx.switchTab({
        url: '/pages/activity/published/published',
        success: function (res) {
          // success
        },
        fail: function () {
          // fail
        },
        complete: function () {
          // complete
        }
      })
      console.log("auth true");

    } else {
      console.log("auth false or init");
    }
  },
  onHide: function () {
    // 页面隐藏
  },
  onUnload: function () {
    // 页面关闭
  },
  reauth: function () {
    app.login(function () {
      wx.switchTab({
        url: '/pages/activity/published/published',
        success: function (res) {
          // success
        },
        fail: function () {
          // fail
        },
        complete: function () {
          // complete
        }
      })
    });
  }
})