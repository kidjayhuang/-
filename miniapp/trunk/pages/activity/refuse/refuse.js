// pages/activity/refuse/refuse.js
var request = require("../../../utils/request.js");
Page({
  data: {
    remark: "",
    button: {
      name: '确定',
      handle: 'refuse',
      disable: true
    }
  },
  onLoad: function (options) {
    // 页面初始化 options为页面跳转所带来的参数
    var activityId = options.id;
    var verifyId = options.verify;
    this.setData({
      id: activityId,
      verifyId: verifyId
    });
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
  bindRemarkInput: function (event) {
    this.setData({
      remark: event.detail.value
    });

    this.check();
  },
  check: function () {
    var remark = this.data.remark;

    if (remark == "") {
      this.setData({"button.disable": true});
    } else {
      this.setData({"button.disable": false});
    }
  },
  refuse: function () {
    var that = this;
    var url = "https://api.sca.programplus.cn/activity/verify";
    var activityId = this.data.id;
    var verifyId = this.data.verifyId;
    var result = 102;
    var remark = this.data.remark;
    var postData = {
      verify_id: verifyId,
      result: result,
      remark: remark
    };

    this.setData({"button.disable": true});

    request.request(that, url, postData, function (data) {
      request.setExpire("https://api.sca.programplus.cn/activity/my_create", {page: 1});
      request.setExpire("https://api.sca.programplus.cn/activity/notice", {page: 1});
      request.setExpire("https://api.sca.programplus.cn/activity/member", {activity_id: activityId});
      request.setExpire("https://api.sca.programplus.cn/activity/member_rich", {activity_id: activityId});
      
      wx.navigateBack({
        delta: 2
      });
    }, function () {
      that.setData({"button.disable": false});
    });
  }
})