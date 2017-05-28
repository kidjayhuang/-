// pages/mine/message/detail/detail.js
var request = require("../../../../utils/request.js");
var app = getApp();
Page({
  data: {
    notice: {}
  },
  onLoad: function (options) {
    // 页面初始化 options为页面跳转所带来的参数
    var type = options.type;
    var activityId = options.id;
    var userId = options.user;
    var verifyId = options.verify;

    this.setData({
      type: type,
      id: activityId,
      userId: userId,
      verifyId: verifyId
    });

    if (type == "remark") {
      this.loadVerifyRemark();
    } else {
      this.loadVerifyInfo();
    }
  },
  onReady: function () {
    // 页面渲染完成
  },
  onShow: function () {
    // 页面显示
  },
  onHide: function () {
    // 页面隐藏
  },
  onUnload: function () {
    // 页面关闭
  },
  loadVerifyRemark: function () {
    var that = this;
    var url = "https://api.sca.programplus.cn/activity/verify_remark";
    var activityId = this.data.id;
    var userId = this.data.userId;
    var postData = {
      activity_id: activityId,
      user_id: userId
    }

    request.request(that, url, postData, function(data) {
      that.setData({verifyData: data});
    }, function() {

    });

  },
  loadVerifyInfo: function () {
    var that = this;
    var url = "https://api.sca.programplus.cn/activity/verify_info";
    var verifyId = this.data.verifyId;
    var postData = {
      verify_id: verifyId
    }

    request.request(that, url, postData, function(data) {
      that.setData({verifyData: data});
    }, function() {

    });

  },
  agree: function () {
    var that = this;
    var url = "https://api.sca.programplus.cn/activity/verify";
    var verifyId = this.data.verifyId;
    var activityId = this.data.id;
    var result = 101;
    var remark = "";
    var postData = {
      verify_id: verifyId,
      result: result,
      remark: remark
    };

    request.request(that, url, postData, function (data) {
      request.setExpire("https://api.sca.programplus.cn/activity/my_create", {page: 1});
      request.setExpire("https://api.sca.programplus.cn/activity/notice", {page: 1});
      request.setExpire("https://api.sca.programplus.cn/activity/member", {activity_id: activityId});
      request.setExpire("https://api.sca.programplus.cn/activity/member_rich", {activity_id: activityId});
      
      wx.navigateBack({
      delta: 1
    })

    }, function () {

    });
  },
  refuse: function () {
    var activityId = this.data.id;
    var verifyId = this.data.verifyId;
    wx.navigateTo({
      url: '/pages/activity/refuse/refuse?id=' + activityId + '&verify=' + verifyId
    })
  },
  kick: function () {
    var that = this;
    var url = "https://api.sca.programplus.cn/activity/kick";
    var activityId = this.data.id;
    var userId = this.data.userId;
    var postData = {
      activity_id: activityId,
      user_id: userId
    };

    wx.showModal({
      title: '提示',
      content: '是否要将该用户移出本活动',
      success: function (res) {
        if (res.confirm) {
          console.log('用户点击确定')

          request.request(this, url, postData, function(data) {
            request.setExpire("https://api.sca.programplus.cn/activity/my_create", {page: 1});
            request.setExpire("https://api.sca.programplus.cn/activity/member", {activity_id: activityId});
            request.setExpire("https://api.sca.programplus.cn/activity/member_rich", { activity_id: activityId });

             wx.showToast({
              title: '移出活动成功',
              icon: 'success',
              duration: 2000
            })

            wx.navigateBack();
          }, function() {

          });
        }
      }
    })
  }
})