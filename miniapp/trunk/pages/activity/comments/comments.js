var request = require("../../../utils/request.js");

Page({
  data: {
    tip: {
      title: "你来发表第一个评论吧",
      image: "/images/bg-empty.png"
    },
    toUserId: 0,
    toUserName: "",
    toUserIndex: 0,
    content: "",
    replyContent: "",
    page: 1,
    isLoading: false,
    isFirst: true,
    replying: false
  },
  onLoad: function (options) {
    // 页面初始化 options为页面跳转所带来的参数
    var activityId = options.id;

    this.setData({
      id: activityId,
    });

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
  },
  onReachBottom: function () {
    var isLoading = this.data.isLoading;

    if (isLoading) {
      console.log("数据加载中");
    } else {
      this.loadMoreData();
    }

  },
  onPullDownRefresh: function () {
    this.reloadData();
  },
  initData: function () {
    this.loadComment();
  },
  refreshData: function () {
    if (this.data.isFirst) {
      this.setData({isFirst: false});
    } else {
      this.refreshComment();
    }
    
  },
  reloadData: function () {
    this.reloadComment();

    wx.stopPullDownRefresh();
  },
 
  loadComment: function () {
    var that = this;
    var activityId = this.data.id;

    var url = "https://api.sca.programplus.cn/activity/reply_list";
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

    var url = "https://api.sca.programplus.cn/activity/reply_list";
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

    var url = "https://api.sca.programplus.cn/activity/reply_list";
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
    var url = "https://api.sca.programplus.cn/activity/reply_list";
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
  bindReplyInput: function (event) {
    var value = event.detail.value;

    this.setData({ content: value });
  },
  bindReplyLineChange: function (event) {
    var lineCount = event.detail.lineCount;
    var count = lineCount > 5 ? 5 : lineCount;
    var lineHeight = event.detail.height / count;
    var height = count * lineHeight;
    this.setData({ replyHeight: height });
    console.log(event.detail);
  },
  selectReply: function (event) {
    var author = event.currentTarget.dataset.author;

    if (author == 1) {
      var that = this;
      var replyId = event.currentTarget.dataset.reply;
      wx.showActionSheet({
        itemList: ['删除'],
        success: function (res) {
          if (!res.cancel) {
            console.log(res.tapIndex)
            that.deleteReply(replyId);
          }
        }
      })
    } else {
      var userId = event.currentTarget.dataset.id;
      var userName = event.currentTarget.dataset.name;
      var userIndex = event.currentTarget.dataset.index;
      this.setData({
        toUserId: userId,
        toUserName: userName,
        toUserIndex: userIndex
      });
    }
  },
  cancelSelect: function (event) {

    this.setData({
      toUserId: 0,
      toUserName: "",
      toUserIndex: 0
    });
  },
  reply: function () {
    if (!this.data.replying) {
      var that = this;
      var activityId = this.data.id;
      var url = "https://api.sca.programplus.cn/activity/reply";
      var type = 101;//102
      var toUserId = this.data.toUserId;
      if (toUserId == 0) {
        type = 101;
      } else {
        type = 102;
      }
      var content = this.data.content;

      if (content == "") {

      } else {
        var postData = {
          activity_id: activityId,
          type: type,
          to_user_id: toUserId,
          content: content
        };

        this.setData({replying: true});

        request.request(this, url, postData, function (data) {
          console.log("reply activity success.");
          that.setData({
            replyContent: "",
            content: "",
            toUserId: 0,
            toUserName: "",
            toUserIndex: 0
          });

          that.setData({replying: false});
          
          request.setExpire("https://api.sca.programplus.cn/activity/reply_list", { activity_id: activityId, page: 1 });

          that.loadComment();
        }, function () {
          that.setData({replying: false});
        });
      }
    }
  },
  deleteReply: function (replyId) {
    var that = this;
    var activityId = this.data.id;
    var url = "https://api.sca.programplus.cn/activity/reply_del";

    var postData = {
      activity_id: activityId,
      reply_id: replyId
    };

    request.request(this, url, postData, function (data) {
      console.log("delete reply activity success.");
      that.setData({
        replyContent: "",
        content: "",
        toUserId: 0,
        toUserName: "",
        toUserIndex: 0
      });

      request.setExpire("https://api.sca.programplus.cn/activity/reply_list", { activity_id: activityId, page: 1 });

      that.loadComment();
    }, function () {

    });

  }
})