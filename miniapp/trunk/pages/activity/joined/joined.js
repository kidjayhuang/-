// pages/activity/joined/joined.js
var request = require("../../../utils/request.js");
var notify = require("../../../utils/notify.js");

var app = getApp();

Page({
  data:{
    message: {
      unreadCount: 10,
      handle: "showMessage",
      type: "新消息"
    },
    tip: {
      title: "你还没有参加过小城活动",
      image: "/images/bg-empty.png"
    },
    activities: {
    },
    page: 1,
    isLoading: false,
    isFirst: true
  },
  onLoad:function(options){
    // 页面初始化 options为页面跳转所带来的参数
    var auth = app.globalData.auth;
    this.setData({auth: auth});

    var that = this;
    notify.addObserver("auth", function(data) {
      that.setData({"auth": data});
      that.initData();
    }, that);

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
    var that = this;
    notify.removeObserver("auth", that);
  },
  onReachBottom: function () {
    var isLoading = this.data.isLoading;

    var auth = app.globalData.auth;
    this.setData({auth: auth});

    if(isLoading) {
      console.log("数据加载中");
    } else {
      if (auth == 1) {
        this.loadMoreActivities();
      }
    }
    
  },
  onPullDownRefresh: function () {
    this.reloadData();
  },
  initData: function() {
    var auth = app.globalData.auth;
    this.setData({auth: auth});

    if (auth == 1) {
      this.loadActivities();
    }
  },
  refreshData: function () {
    var auth = app.globalData.auth;
    this.setData({auth: auth});

    if (auth == 1) {
      if (this.data.isFirst) {
        this.setData({isFirst: false});
      } else {
        this.refreshActivities();
      }
    }
  },
  reloadData: function() {
    var auth = app.globalData.auth;
    this.setData({auth: auth});

    if (auth == 1) {
      this.reloadActivities();
    }
    wx.stopPullDownRefresh();
  },
  loadActivities: function () {
    var that = this;
    var url = "https://api.sca.programplus.cn/activity/my_join";
    var postData = {
      page: 1
    };
    var dataKey = "activities";

    request.initDataWithCallback(this, url, postData, dataKey, function (data) {
      that.setData({
        page: 2
      });

      for (var i = 0; i < data.list.length; i++) {
        var activity = data.list[i];
        var sumCount = activity.sum_count;
        var memberCount = activity.member_count;
        var array = new Array();
        var arrayLength = sumCount > 10 ? 10 - memberCount : sumCount - memberCount;
        for (var j = 0; j < arrayLength; j++) {
          array.push(0);
        }
        if (arrayLength > 0 && sumCount > 10) {
          array[arrayLength - 1] = sumCount - 10 > 99 ? 99 : sumCount - 10;
        }
        var temp = {};
        temp["activities.list[" + i + "].array"] = array;
        that.setData(temp);
      }
    }, function () {

    });
  },
  reloadActivities: function () {
    var that = this;
    var url = "https://api.sca.programplus.cn/activity/my_join";
    var postData = {
      page: 1
    };
    var dataKey = "activities";

    request.loadDataWithCallback(this, url, postData, dataKey, function (data) {
      that.setData({
        page: 2
      });

      for (var i = 0; i < data.list.length; i++) {
        var activity = data.list[i];
        var sumCount = activity.sum_count;
        var memberCount = activity.member_count;
        var array = new Array();
        var arrayLength = sumCount > 10 ? 10 - memberCount : sumCount - memberCount;
        for (var j = 0; j < arrayLength; j++) {
          array.push(0);
        }
        if (arrayLength > 0 && sumCount > 10) {
          array[arrayLength - 1] = sumCount - 10 > 99 ? 99 : sumCount - 10;
        }
        var temp = {};
        temp["activities.list[" + i + "].array"] = array;
        that.setData(temp);
      }
    }, function () {

    });
  },
  refreshActivities: function () {
    var that = this;
    var url = "https://api.sca.programplus.cn/activity/my_join";
    var postData = {
      page: 1
    };
    var dataKey = "activities";

    request.refreshDataWithCallback(this, url, postData, dataKey, function (data, flag) {
      if (flag) {
        that.setData({
          page: 2
        });

        for (var i = 0; i < data.list.length; i++) {
          var activity = data.list[i];
          var sumCount = activity.sum_count;
          var memberCount = activity.member_count;
          var array = new Array();
          var arrayLength = sumCount > 10 ? 10 - memberCount : sumCount - memberCount;
          for (var j = 0; j < arrayLength; j++) {
            array.push(0);
          }
          if (arrayLength > 0 && sumCount > 10) {
            array[arrayLength - 1] = sumCount - 10 > 99 ? 99 : sumCount - 10;
          }
          var temp = {};
          temp["activities.list[" + i + "].array"] = array;
          that.setData(temp);
        }
      }
    }, function () {

    });
  },
  loadMoreActivities: function() {
    this.setData({isLoading: true});
    var that = this;
    var page = this.data.page;
    var url = "https://api.sca.programplus.cn/activity/my_join";
    var postData = {
      page :page
    };

    request.request(this, url, postData, function(data) {

      if(data.list.length > 0) {
        var list = that.data.activities.list;
        var newList = list.concat(data.list);

        that.setData({"activities.list": newList });
        that.setData({page: page + 1});
      }
      that.setData({isLoading: false});
    }, function() {
      that.setData({isLoading: false});
    });
  },
  create:function() {
    wx.navigateTo({url: "/pages/activity/create/create"});
  },
  showMessage: function() {
    wx.navigateTo({url: "/pages/activity/message/message"});
  },
  showActivity: function(event) {
    var activityId = event.currentTarget.dataset.id;
    wx.navigateTo({url: "/pages/activity/detail/detail?id=" + activityId});
  }
})