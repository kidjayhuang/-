// pages/activity/join/join.js
var request = require("../../../utils/request.js");
Page({
  data: {
    remark: "",
    button: {
      name: '确定',
      handle: 'joinActivity',
      disable: true
    },
    request: ""
  },
  onLoad: function (options) {
    // 页面初始化 options为页面跳转所带来的参数
    var activityId = options.id;
    var verifyRequest = options.request;
    this.setData({
     id: activityId,
     request: verifyRequest
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
    var value = event.detail.value;
    this.setData({remark: value});

    this.check();
  },
  check: function () {
    var remark = this.data.remark;

    if (remark = "") {
      this.setData({"button.disable": true});
    } else {
      this.setData({"button.disable": false});
    }
  },
  joinActivity: function () {
    var that = this;
    var url = "https://api.sca.programplus.cn/activity/join";
    var activityId = this.data.id;
    var remark = this.data.remark;
    var postData = {
      activity_id: activityId,
      remark: remark
    };

    this.setData({"button.disable": true});

    request.request(that, url, postData, function (data) {
      console.log(data);
      if (data.result == 1) {
        request.setExpire("https://api.sca.programplus.cn/activity/my_join", {page: 1});
        request.setExpire("https://api.sca.programplus.cn/activity/member", {activity_id: activityId});
        request.setExpire("https://api.sca.programplus.cn/activity/member_rich", {activity_id: activityId});

        wx.showToast({
          title: '申请成功',
          icon: 'success',
          duration: 2000
        })
      } else {
        request.setExpire("https://api.sca.programplus.cn/activity/my_join", {page: 1});
        request.setExpire("https://api.sca.programplus.cn/activity/member", {activity_id: activityId});
        request.setExpire("https://api.sca.programplus.cn/activity/member_rich", {activity_id: activityId});

        wx.showToast({
          title: '申请审核中',
          icon: 'success',
          duration: 2000
        })
      }
      wx.navigateBack({
        delta: 1
      })
    }, function () {
      that.setData({"button.disable": false});
    });
  }
})