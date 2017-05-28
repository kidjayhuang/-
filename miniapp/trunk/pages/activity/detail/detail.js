// pages/article/article.js
var request = require("../../../utils/request.js");
var notify = require("../../../utils/notify.js");

var app = getApp();

Page({
  data: {
    activity: {},
    members: {
      member: [],
      verify: []
    },
    isFirst: true,
    joinButton: {
      name: '加入',
      handle: 'joinActivity',
      disable: true
    },
    quitButton: {
      name: '退出',
      handle: 'quitActivity',
      disable: true
    },
    manageButton: {
      name: '管理',
      handle: 'manageActivity',
      disable: false
    },
    replyCount: 0
  },
  onLoad: function (options) {
    // 页面初始化 options为页面跳转所带来的参数
    var activityId = options.id;

    this.setData({
      id: activityId
    });

    var auth = app.globalData.auth;
    this.setData({auth: auth});

    var that = this;
    notify.addObserver("auth", function(data) {
      that.setData({"auth": data});
      that.initData();
    }, that);

    this.initData();
  },
  onReady: function () {
    // 页面渲染完成
  },
  onShow: function () {
    // 页面显示
    this.refreshData();
  },
  onHide: function () {
    // 页面隐藏
  },
  onUnload: function () {
    // 页面关闭
    var that = this;
    notify.removeObserver("auth", that);
  },
  onPullDownRefresh: function () {
    this.reloadData();
  },
  onShareAppMessage: function () {
    var auth = app.globalData.auth;
    this.setData({auth: auth});

    if (auth == 1) {
      var activityId = this.data.id;
      var title = this.data.activity.title | "小城活动";
      var desc = "小城活动，发布活动真方便";

      return {
        title: title,
        desc: desc,
        path: '/pages/activity/detail/detail?id=' + activityId
      }
    } else {

    }
  },
  initData: function () {
    var auth = app.globalData.auth;
    this.setData({auth: auth});

    if (auth == 1) {
      this.loadActivityStatus();
      this.loadActivity();
      this.loadReplyCount();
      this.loadMembers();
    }
  },
  refreshData: function () {
    var auth = app.globalData.auth;
    this.setData({auth: auth});

    if (auth == 1) {
      if (this.data.isFirst) {
        this.setData({isFirst: false});
      } else {
        this.loadActivityStatus();
        this.refreshActivity();
        this.loadReplyCount();
        this.refreshMembers();
      }
    }
  },
  reloadData: function () {
    var auth = app.globalData.auth;
    this.setData({auth: auth});

    if (auth == 1) {
      this.loadActivityStatus();
      this.reloadActivity();
      this.loadReplyCount();
      this.reloadMembers();
    }

    wx.stopPullDownRefresh();
  },
  loadActivityStatus: function () {
    var that = this;
    var activityId = this.data.id;

    var url = "https://api.sca.programplus.cn/activity/get_status";
    var postData = {
      activity_id: activityId
    };

    request.request(this, url, postData, function(data) {
      that.setData({status: data.status});
    }, function() {

    });
  },
  loadReplyCount: function () {
    var that = this;
    var activityId = this.data.id;

    var url = "https://api.sca.programplus.cn/activity/reply_count";
    var postData = {
      activity_id: activityId
    };

    request.request(this, url, postData, function(data) {
      that.setData({replyCount: data.count});
    }, function() {

    });
  },
  loadActivity: function () {
    var that = this;
    var activityId = this.data.id;

    var url = "https://api.sca.programplus.cn/activity/rich_profile";
    var postData = {
      activity_id: activityId
    };
    var dataKey = "activity";

    request.initDataWithCallback(this, url, postData, dataKey, function (data) {
      
    }, function () {

    });
  },
  reloadActivity: function () {
    var that = this;
    var activityId = this.data.id;

    var url = "https://api.sca.programplus.cn/activity/rich_profile";
    var postData = {
      activity_id: activityId
    };
    var dataKey = "activity";

    request.loadDataWithCallback(this, url, postData, dataKey, function (data) {

    }, function () {

    });
  },
  refreshActivity: function () {
    var that = this;
    var activityId = this.data.id;

    var url = "https://api.sca.programplus.cn/activity/rich_profile";
    var postData = {
      activity_id: activityId
    };
    var dataKey = "article";

    request.refreshDataWithCallback(this, url, postData, dataKey, function (data, flag) {
      
    }, function () {

    });
  },
  loadMembers: function () {
    var that = this;
    var activityId = this.data.id;
    var url = "https://api.sca.programplus.cn/activity/member";
    var postData = {
      activity_id: activityId
    };
    var dataKey = "members";

    request.initData(this, url, postData, dataKey);
  },
  reloadMembers: function () {
    var that = this;
    var activityId = this.data.id;
    var url = "https://api.sca.programplus.cn/activity/member";
    var postData = {
      activity_id: activityId
    };
    var dataKey = "members";

    request.loadData(this, url, postData, dataKey);
  },
  refreshMembers: function () {
    var that = this;
    var activityId = this.data.id;

    var url = "https://api.sca.programplus.cn/activity/member";
    var postData = {
      activity_id: activityId
    };
    var dataKey = "members";

    request.refreshData(this, url, postData, dataKey);
  },
  loadComment: function () {
    var that = this;
    var activityId = this.data.id;

    var url = "https://api.sca.programplus.cn/article/reply_list";
    var postData = {
      activity_id: activityId,
      page: 1
    };
    var dataKey = "comments";

    request.initDataWithCallback(this, url, postData, dataKey, function (data) {
      that.setData({ page: 2 });
    }, function () {

    });
  },
  reloadComment: function () {
    var that = this;
    var activityId = this.data.id;

    var url = "https://api.sca.programplus.cn/article/reply_list";
    var postData = {
      activity_id: activityId,
      page: 1
    };
    var dataKey = "comments";

    request.loadDataWithCallback(this, url, postData, dataKey, function (data) {
      that.setData({ page: 2 });
    }, function () {

    });
  },
  refreshComment: function () {
    var that = this;
    var activityId = this.data.id;

    var url = "https://api.sca.programplus.cn/article/reply_list";
    var postData = {
      activity_id: activityId,
      page: 1
    };
    var dataKey = "comments";

    request.refreshDataWithCallback(this, url, postData, dataKey, function (data, flag) {
      if (flag) {
        that.setData({ page: 2 });
      }
    }, function () {

    });
  },
  loadMoreData: function () {
    this.setData({ isLoading: true });
    var that = this;
    var activityId = this.data.id;
    var page = this.data.page;
    var url = "https://api.sca.programplus.cn/article/reply_list";
    var postData = {
      activity_id: activityId,
      page: page
    };

    request.request(this, url, postData, function (data) {

      if (data.length > 0) {
        var list = that.data.comments;
        var newList = list.concat(data);

        that.setData({ comments: newList });
        that.setData({ page: page + 1 });
      }
      that.setData({ isLoading: false });
    }, function () {
      that.setData({ isLoading: false });
    });
  },
  joinActivity: function () {
    var activityId = this.data.id;
    var verifyRequest = this.data.activity.verify_request;

    wx.navigateTo({url: "/pages/activity/join/join?id=" + activityId + "&request=" + verifyRequest});
  },
  quitActivity: function () {
    var that = this;
    var url = "https://api.sca.programplus.cn/activity/quit";
    var activityId = this.data.id;
    var postData = {
      activity_id: activityId
    };

    wx.showModal({
      title: '提示',
      content: '是否要退出本活动',
      success: function (res) {
        if (res.confirm) {
          console.log('用户点击确定')
          request.request(that, url, postData, function (data) {

            request.setExpire("https://api.sca.programplus.cn/activity/my_join", {page: 1});
            request.setExpire("https://api.sca.programplus.cn/activity/member", {activity_id: activityId});
            request.setExpire("https://api.sca.programplus.cn/activity/member_rich", {activity_id: activityId});

            wx.showToast({
              title: '退出成功',
              icon: 'success',
              duration: 2000
            })

            wx.navigateBack();
          }, function () {

          });
        }
      }
    })
  },
  showAlbum: function (event) {
    var index = event.currentTarget.dataset.index;
    var urls = this.data.activity.pic_list;
    var current = urls[index];

    wx.previewImage({
      current: current, // 当前显示图片的http链接
      urls: urls // 需要预览的图片http链接列表
    })
  },
  manageActivity: function (event) {
    var that = this;
    var itemList = [];
    var handleList = [];
    var status = this.data.status;

    /*
    101 正在报名 
    操作：停止报名、结束、取消
    110 停止报名 
    操作：恢复报名、结束、取消
    120 活动结束 
    操作：评价（暂时不错）
    200 取消 
    操作：无
    只有“正在报名”状态可以报名
    */
    if (status == 101) {
      itemList = ['停止报名', '结束活动', '取消活动', '管理成员'];
      handleList = [that.stopJoin, that.stopActivity, that.cancelActivity, that.manageMember];
    } else if (status == 110) {
      itemList = ['恢复报名', '结束活动', '取消活动', '管理成员'];
      handleList = [that.resumeJoin, that.stopActivity, that.cancelActivity, that.manageMember];
    } else if (status == 120) {
      return;
    } else if (status == 200) {
      return;
    } else {
      return;
    }
    
    
    wx.showActionSheet({
      itemList: itemList,
      success: function (res) {
        if (!res.cancel) {
          console.log(res.tapIndex)
          handleList[res.tapIndex]();
        }
      }
    })
  },
  stopActivity: function () {
    var that = this;
    var activityId = this.data.id;
    var url = "https://api.sca.programplus.cn/activity/set_status";
    var status = 120;
    var postData = {
      activity_id: activityId,
      status: status
    };

    request.request(this, url, postData, function (data) {
      that.setData({status: status});
      request.setExpire("https://api.sca.programplus.cn/activity/my_create", {page: 1});
      wx.showToast({
        title: '结束活动成功',
        icon: 'success',
        duration: 2000
      })

    }, function () {

    });
  },
  stopJoin: function () {
    var that = this;
    var activityId = this.data.id;
    var url = "https://api.sca.programplus.cn/activity/set_status";
    var status = 110;
    var postData = {
      activity_id: activityId,
      status: status
    };

    request.request(this, url, postData, function (data) {
      that.setData({status: status});
      request.setExpire("https://api.sca.programplus.cn/activity/my_create", {page: 1});
      wx.showToast({
        title: '停止报名成功',
        icon: 'success',
        duration: 2000
      })

    }, function () {

    });
  },
  resumeJoin: function () {
    var that = this;
    var activityId = this.data.id;
    var url = "https://api.sca.programplus.cn/activity/set_status";
    var status = 101;
    var postData = {
      activity_id: activityId,
      status: status
    };

    request.request(this, url, postData, function (data) {
      that.setData({status: status});
      request.setExpire("https://api.sca.programplus.cn/activity/my_create", {page: 1});
      wx.showToast({
        title: '恢复报名成功',
        icon: 'success',
        duration: 2000
      })

    }, function () {

    });
  },
  cancelActivity: function () {
    var that = this;
    var activityId = this.data.id;
    var url = "https://api.sca.programplus.cn/activity/set_status";
    var status = 200;
    var postData = {
      activity_id: activityId,
      status: status
    };

    request.request(this, url, postData, function (data) {
      that.setData({status: status});
      request.setExpire("https://api.sca.programplus.cn/activity/my_create", {page: 1});
      wx.showToast({
        title: '取消成功',
        icon: 'success',
        duration: 2000
      })

    }, function () {

    });
  },
  manageMember: function () {
    var activityId = this.data.id;
    wx.navigateTo({url: "/pages/activity/member/member?id=" + activityId});
  },
  showComments: function () {
    var activityId = this.data.id;
    wx.navigateTo({url: "/pages/activity/comments/comments?id=" + activityId});
  },
  showMap: function () {
    var latitude = parseFloat(this.data.activity.latitude);
    var longitude = parseFloat(this.data.activity.longitude);
    var name = this.data.activity.title;
    var address = this.data.activity.address;

    wx.openLocation({
      latitude: latitude, // 纬度，范围为-90~90，负数表示南纬
      longitude: longitude, // 经度，范围为-180~180，负数表示西经
      scale: 28, // 缩放比例
      name: name, // 位置名
      address: address, // 地址的详细说明
      success: function(res){
        // success
      },
      fail: function() {
        // fail
      },
      complete: function() {
        // complete
      }
    })
  }
})