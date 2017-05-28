var request = require("../../../utils/request.js");
var notify = require("../../../utils/notify.js");

var app = getApp();

Page({
  data:{
    tip: {
      title: "附近暂无活动，我来发布一个！",
      image: "/images/bg-near.png"
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

    var location = app.globalData.location;
    this.setData({location: location});

    var that = this;
    notify.addObserver("auth", function(data) {
      that.setData({"auth": data});
      that.initData();
    }, that);

    notify.addObserver("location", function(data) {
      that.setData({"location": data});
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
    notify.removeObserver("location", that);
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
  onShareAppMessage: function () {
    var activityId = this.data.id;
    var title = "小城活动";
    var desc = "发布活动真方便";

    return {
      title: title,
      desc: desc,
      path: '/pages/activity/near/near'
    }
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
    var url = "https://api.sca.programplus.cn/lbs/nearby";
    var location = app.globalData.location;
    this.setData({location: location});

    var postData = {
      longitude: location.longitude,
      latitude: location.latitude,
      page: 1
    };
    var dataKey = "activities";

    request.initDataWithCallback(this, url, postData, dataKey, function (data) {
      that.setData({
        page: 2
      });

    }, function () {

    });
  },
  reloadActivities: function () {
    var that = this;
    var url = "https://api.sca.programplus.cn/lbs/nearby";
    var location = app.globalData.location;
    this.setData({location: location});

    var postData = {
      longitude: location.longitude,
      latitude: location.latitude,
      page: 1
    };
    var dataKey = "activities";

    request.loadDataWithCallback(this, url, postData, dataKey, function (data) {
      that.setData({
        page: 2
      });

    }, function () {

    });
  },
  refreshActivities: function () {
    var that = this;
    var url = "https://api.sca.programplus.cn/lbs/nearby";
    var location = app.globalData.location;
    this.setData({location: location});

    var postData = {
      longitude: location.longitude,
      latitude: location.latitude,
      page: 1
    };
    var dataKey = "activities";

    request.refreshDataWithCallback(this, url, postData, dataKey, function (data, flag) {
      if (flag) {
        that.setData({
          page: 2
        });

      }
    }, function () {

    });
  },
  loadMoreActivities: function() {
    this.setData({isLoading: true});
    var that = this;
    var page = this.data.page;
    var url = "https://api.sca.programplus.cn/lbs/nearby";
    var location = app.globalData.location;
    this.setData({location: location});

    var postData = {
      longitude: location.longitude,
      latitude: location.latitude,
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
  },
  chooseLocation: function () {
    var that = this;
    wx.chooseLocation({
      success: function (res) {
        // success
        that.setData({ location: res });
        app.globalData.location.name = res.name;
        app.globalData.location.address = res.address;
        app.globalData.location.longitude = res.longitude;
        app.globalData.location.latitude = res.latitude;
        that.reloadData();
      },
      fail: function (res) {
        // fail
        console.log(res);
        if (res.errMsg == "chooseLocation:fail auth deny") {
          wx.showToast({
            title: '您拒绝了获取地址信息',
            icon: 'success',
            duration: 2000
          })
        }
      },
      complete: function () {
        // complete
      }
    })
  }
})