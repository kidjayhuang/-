var request = require("../../../utils/request.js");
Page({
  data:{
    members: {
      member: [],
      verify: []
    },
    isLoading: false,
    isFirst: true
  },
  onLoad:function(options){
    // 页面初始化 options为页面跳转所带来的参数
    var activityId = options.id;
    this.setData({id: activityId});

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
    this.reloadData();
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
    this.loadMember();
  },
  refreshData: function () {
    if (this.data.isFirst) {
      this.setData({isFirst: false});
    } else {
      this.refreshMember();
    }
  },
  reloadData: function () {
    this.reloadMember();
    wx.stopPullDownRefresh();
  },
  loadMember: function () {
    var that = this;
    var activityId = this.data.id;
    var url = "https://api.sca.programplus.cn/activity/member_rich";
    var postData = {
      activity_id: activityId
    };
    var dataKey = "members";

    request.initDataWithCallback(this, url, postData, dataKey, function(data) {

    }, function() {

    });
  },
  refreshMember: function () {
    var that = this;
    var activityId = this.data.id;
    var url = "https://api.sca.programplus.cn/activity/member_rich";
    var postData = {
      activity_id: activityId
    };
    var dataKey = "members";

    request.refreshDataWithCallback(this, url, postData, dataKey, function(data, flag) {

    }, function() {

    });
  },
  reloadMember: function () {
    var that = this;
    var activityId = this.data.id;
    var url = "https://api.sca.programplus.cn/activity/member_rich";
    var postData = {
      activity_id: activityId
    };
    var dataKey = "members";

    request.loadDataWithCallback(this, url, postData, dataKey, function(data) {

    }, function() {

    });
  },
  showVerifyRemark:function(event) {
    var activityId = this.data.id;
    var userId = event.currentTarget.dataset.id;

    wx.navigateTo({
      url: '/pages/activity/message/detail/detail?type=remark&id=' + activityId + "&user=" + userId
    })
  },
  showVerifyInfo:function(event) {
    var activityId = this.data.id;
    var verifyId = event.currentTarget.dataset.verify;

    wx.navigateTo({
      url: '/pages/activity/message/detail/detail?type=info&id=' + activityId + '&verify=' + verifyId
    })
  }
})