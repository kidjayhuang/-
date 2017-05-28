// pages/mine/message/message.js
var request = require("../../../utils/request.js");
var app = getApp();
Page({
  data:{
    messages: {
    },
    tip: {
      title: "你还没有收到消息",
      image: "/images/bg-empty.png"
    },
    page: 1,
    isLoading: false,
    isFirst: true
  },
  onLoad:function(options){
    // 页面初始化 options为页面跳转所带来的参数

    this.initData();
  },
  onReady:function(){
    // 页面渲染完成
  },
  onShow:function(){
    // 页面显示
    this.refreshData();
  },
  onHide:function(){
    // 页面隐藏
  },
  onUnload:function(){
    // 页面关闭
  },
  onPullDownRefresh: function () {
    this.reloadMessage();
    wx.stopPullDownRefresh();
  },
  onReachBottom: function () {
    var isLoading = this.data.isLoading;

    if(isLoading) {
      console.log("数据加载中");
    } else {
      this.loadMoreData();
    }
    
  },
  initData: function () {
    this.loadMessage();
  },
  refreshData: function () {
    if (this.data.isFirst) {
      this.setData({isFirst: false});
    } else {
      this.refreshMessage();
    }
  },
  loadMessage: function () {
    var that = this;
    var url = "https://api.sca.programplus.cn/activity/notice";
    var page = this.data.page;
    var postData = {
      page: 1
    };
    var dataKey = "messages";

    request.initDataWithCallback(this, url, postData, dataKey, function(data) {
      that.setData({page: 2});
    }, function() {

    });
  },
  reloadMessage: function () {
    var that = this;
    var url = "https://api.sca.programplus.cn/activity/notice";
    var page = this.data.page;
    var postData = {
      page: 1
    };
    var dataKey = "messages";

    request.loadDataWithCallback(this, url, postData, dataKey, function(data) {
      that.setData({page: 2});
    }, function() {

    });
  },
  refreshMessage: function () {
    var that = this;
    var url = "https://api.sca.programplus.cn/activity/notice";
    var page = this.data.page;
    var postData = {
      page: 1
    };
    var dataKey = "messages";

    request.refreshDataWithCallback(this, url, postData, dataKey, function(data, flag) {
      if (flag) {
        that.setData({page: 2});
      }
    }, function() {

    });
  },
  loadMoreData: function () {
    this.setData({isLoading: true});
    var that = this;
    var page = this.data.page;
    var url = "https://api.sca.programplus.cn/activity/notice";
    var postData = {
      page: page
    };

    request.request(this, url, postData, function(data) {

      if(data.list.length > 0) {
        var list = that.data.messages.list;
        var newList = list.concat(data.list);

        that.setData({"messages.list": newList });
        that.setData({page: page + 1});
      }
      that.setData({isLoading: false});
    }, function() {
      that.setData({isLoading: false});
    });
  },
  showMessageDetail:function(event) {
    var activityId = event.currentTarget.dataset.activity;
    var verifyId = event.currentTarget.dataset.id;

    wx.navigateTo({
      url: '/pages/activity/message/detail/detail?type=info&id=' + activityId + '&verify=' + verifyId
    })
  },
  agree:function(event) {
    var that = this;
    var url = "https://api.sca.programplus.cn/activity/verify";
    var verifyId = event.currentTarget.dataset.id;
    var activityId = event.currentTarget.dataset.activity;
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

      that.loadMessage();
    }, function () {

    });
  }
})