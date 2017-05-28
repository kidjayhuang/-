// pages/activity/create/create.js
var request = require("../../../utils/request.js");
var util = require("../../../utils/util.js");
var app = getApp();

Page({
  data: {
    title: "",
    titleValue: "",
    desc: "",
    pics: [],
    uploadPics: [],
    uploadResult: [],
    date: "",
    today: "",
    time: "19:00",
    location: {
      name: "",
      address: "",
      latitude: 0,
      longitude: 0
    },
    memberIndex: 0,
    memberList: [
      "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", 
      "12", "13", "14", "15", "16", "17", "18", "19", "20", "不限"
    ],
    memberValue: [
      "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", 
      "12", "13", "14", "15", "16", "17", "18", "19", "20", "999999"
    ],
    payIndex: 1,
    payList: ["免费", "AA", "我买单"],
    payValue: ["100", "101", "102"],
    costIndex: 1,
    costList: ["自定义", "20以下", "20~50", "50~100", "100~200", "200~500", "500~1000", "1000以上"],
    costValue: [
      [],
      [0, 20],
      [20, 50],
      [50, 100],
      [100, 200],
      [200, 500],
      [500, 1000],
      [1000, 999999]
    ],
    cost: [0, 20],
    isCheck: true,
    question: "",
    button: {
      name: '发布',
      handle: 'createActivity',
      disable: true
    }
  },
  onLoad: function (options) {
    // 页面初始化 options为页面跳转所带来的参数
    this.loadSign();

    var now = new Date();
    var today = util.formatDate(now);
    this.setData({today: today});

    var tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate()+1);  
    var date = util.formatDate(tomorrow);
    this.setData({date: date});

    var location = app.globalData.defaultLocation;
    this.setData({location: location});
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
  loadSign: function () {
    var that = this;
    request.getUploadSign(function (sign) {
      console.log(sign);
      that.setData({ sign: sign });
    }, function () {
      console.log("get sign fail");
    });
  },
  check: function () {
    var title = this.data.title;
    var desc = this.data.desc;
    var uploadPics = this.data.uploadPics;
    var uploadResult = this.data.uploadResult;
    var date = this.data.date;
    var time = this.data.time;
    var location = this.data.location;
    var cost = this.data.cost;

    if (title != "" && desc != "" && date != "" && time != "" && cost.length != 0 
      && location.name != "" && location.address != ""
      && uploadResult.join('').indexOf("0") == -1 && uploadResult.join('').indexOf("2") == -1) {
      this.setData({ "button.disable": false });
    } else {
      this.setData({ "button.disable": true });
    }
  },
  bindTitleInput: function (event) {
    this.setData({
      title: event.detail.value
    });

    this.check();
  },
  clearTitleInput: function (event) {
    this.setData({
      titleValue: "",
      title: ""
    });

    this.check();
  },
  bindDescInput: function (event) {
    this.setData({
      desc: event.detail.value
    });

    this.check();
  },
  bindDateChange: function (event) {
    this.setData({
      date: event.detail.value
    })

    this.check();
  },
  bindTimeChange: function (event) {
    this.setData({
      time: event.detail.value
    })

    this.check();
  },
  chooseLocation: function () {
    var that = this;
    wx.chooseLocation({
      success: function (res) {
        // success
        that.setData({ location: res });

        that.check();
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
  },
  bindAddressInput: function (event) {
    this.setData({
      "location.name": event.detail.value
    });

    this.check();
  },
  bindMemberChange: function (event) {
    this.setData({
      memberIndex: event.detail.value
    })

    this.check();
  },
  bindPayChange: function (event) {
    this.setData({
      payIndex: event.detail.value
    })

    if (event.detail.value == 0) { // 免费
      this.setData({
        costIndex: -1,
        cost: [0, 0]
      });
    }

    this.check();
  },
  bindCostChange: function (event) {
    this.setData({
      costIndex: event.detail.value,
      cost: this.data.costValue[event.detail.value]
    })

    this.check();
  },
  bindCostInput: function (event) {
    var value = event.detail.value == "" ? 0 : event.detail.value;
    this.setData({
      cost: [value, value]
    });

    this.check();
  },
  checkSwitchChange: function (event) {
    this.setData({
      isCheck: event.detail.value
    });
  },
  bindQuestionInput: function (event) {
    this.setData({
      question: event.detail.value
    });
  },
  createActivity: function () {
    var that = this;

    var title = this.data.title;
    var desc = this.data.desc;
    var uploadPics = this.data.uploadPics;
    var uploadResult = this.data.uploadResult;
    var type = "101";
    var search = "1";
    var date = this.data.date;
    var time = this.data.time;
    var location = this.data.location;
    var member = this.data.memberValue[this.data.memberIndex];
    var pay = this.data.payValue[this.data.payIndex];
    var cost = this.data.cost;
    var isCheck = this.data.isCheck;
    var verifyRequest = this.data.question;

    this.setData({ "button.disable": true });

    var url = "https://api.sca.programplus.cn/activity/create";
    var postData = {
      title: title,
      desc: desc,
      pics: JSON.stringify(uploadPics),
      type: type,
      search: search,
      action_date: date,
      action_time: time,
      address: location.address,
      address_detail: location.name,
      latitude: location.latitude,
      longitude: location.longitude,
      member_begin: member,
      member_end: member,
      pay_type: pay,
      price_begin: cost[0],
      price_end: cost[1],
      join_verify: isCheck ? "1" : "0",
      verify_request: verifyRequest
    };
    request.request(that, url, postData, function (data) {

      wx.showToast({
        title: '发布活动成功',
        icon: 'success',
        duration: 2000
      })

      // 刷新我发布的活动列表
      request.setExpire("https://api.sca.programplus.cn/activity/my_create", {page: 1});
      wx.navigateBack();
    }, function () {
      that.setData({ "button.disable": false });
    });
  },
  emptyHandle: function () {

  },
  deletePhoto: function (index) {
    var pics = this.data.pics;
    pics.splice(index, 1);

    var uploadPics = this.data.uploadPics;
    uploadPics.splice(index, 1);

    var uploadResult = this.data.uploadResult;
    uploadResult.splice(index, 1);
    this.setData({
      pics: pics,
      uploadPics: uploadPics,
      uploadResult: uploadResult
    });
    this.check();
  },
  handlePhoto: function (event) {
    var that = this;
    var index = event.currentTarget.dataset.index;
    var itemList = [];
    var handleList = [];
    var uploadResult = this.data.uploadResult[index];
    if (uploadResult == 1) {
      itemList = ['更换图片', '删除图片'];
      handleList = [that.changePhoto, that.deletePhoto];
    } else if (uploadResult == 2) {
      itemList = ['重新上传', '更换图片', '删除图片'];
      handleList = [that.reuploadPhoto, that.changePhoto, that.deletePhoto];
    } else {
      return;
    }
    wx.showActionSheet({
      itemList: itemList,
      success: function (res) {
        if (!res.cancel) {

          handleList[res.tapIndex](index);
        }
      }
    })
  },
  changePhoto: function (index) {
    var that = this;

    wx.chooseImage({
      count: 1, // 最多可以选择的图片张数，默认9
      sizeType: ['original', 'compressed'], // original 原图，compressed 压缩图，默认二者都有
      sourceType: ['album', 'camera'], // album 从相册选图，camera 使用相机，默认二者都有
      success: function (res) {
        // success
        // that.setData({ pics: res.tempFilePaths });
        var uploadResult = that.data.uploadResult;
        var pics = that.data.pics;

        //上传中
        uploadResult[index] = 0;
        var tempFilePath = res.tempFilePaths[0];
        pics[index] = tempFilePath;

        that.setData({ uploadResult: uploadResult });
        that.setData({ pics: pics });

        that.check();

        //上传中
        console.log(res.tempFilePaths[0]);
        var sign = that.data.sign;

        request.uploads(that, index, tempFilePath, sign, function (index, imageUrl) {
          //上传成功
          var uploadResult = that.data.uploadResult;
          uploadResult[index] = 1;
          that.setData({ uploadResult: uploadResult });
          var uploadPics = that.data.uploadPics;
          uploadPics[index] = imageUrl;
          that.setData({ uploadPics: uploadPics });

          that.check();
        }, function (index) {
          //上传失败
          var uploadResult = that.data.uploadResult;
          uploadResult[index] = 2;
          that.setData({ uploadResult: uploadResult });

          that.check();
          wx.showToast({
            title: '上传图片失败',
            icon: 'loading',
            duration: 2000
          })
        });
      },
      fail: function () {
        // fail
      },
      complete: function () {
        // complete
      }
    })
  },
  reuploadPhoto: function (index) {
    var that = this;


    // success
    // that.setData({ pics: res.tempFilePaths });
    var uploadResult = that.data.uploadResult;
    var pics = that.data.pics;

    //上传中
    uploadResult[index] = 0;
    var tempFilePath = pics[index];

    that.setData({ uploadResult: uploadResult });
    that.check();
    //上传中
    var sign = that.data.sign;

    request.uploads(that, index, tempFilePath, sign, function (index, imageUrl) {
      //上传成功
      var uploadResult = that.data.uploadResult;
      uploadResult[index] = 1;
      that.setData({ uploadResult: uploadResult });
      var uploadPics = that.data.uploadPics;
      uploadPics[index] = imageUrl;
      that.setData({ uploadPics: uploadPics });

      that.check();

    }, function (index) {
      //上传失败
      var uploadResult = that.data.uploadResult;
      uploadResult[index] = 2;
      that.setData({ uploadResult: uploadResult });

      that.check();

      wx.showToast({
        title: '上传图片失败',
        icon: 'loading',
        duration: 2000
      })
    });
  },
  choosePhoto: function () {
    var that = this;
    var picsLength = this.data.pics.length;


    wx.chooseImage({
      count: 9, // 最多可以选择的图片张数，默认9
      sizeType: ['original', 'compressed'], // original 原图，compressed 压缩图，默认二者都有
      sourceType: ['album', 'camera'], // album 从相册选图，camera 使用相机，默认二者都有
      success: function (res) {
        // success
        // that.setData({ pics: res.tempFilePaths });
        var uploadResult = that.data.uploadResult;
        var pics = that.data.pics;
        for (var i = 0; i < res.tempFilePaths.length; i++) {
          //上传中
          uploadResult.push(0);
          var tempFilePath = res.tempFilePaths[i];
          pics.push(tempFilePath);
        }
        that.setData({ uploadResult: uploadResult });
        that.setData({ pics: pics });

        that.check();

        for (var i = 0; i < res.tempFilePaths.length; i++) {
          //上传中
          console.log(res.tempFilePaths[i]);
          var tempFilePath = res.tempFilePaths[i];
          var sign = that.data.sign;

          request.uploads(that, i + picsLength, tempFilePath, sign, function (index, imageUrl) {
            //上传成功
            var uploadResult = that.data.uploadResult;
            uploadResult[index] = 1;
            that.setData({ uploadResult: uploadResult });
            var uploadPics = that.data.uploadPics;
            uploadPics[index] = imageUrl;
            that.setData({ uploadPics: uploadPics });

            that.check();

          }, function (index) {
            //上传失败
            var uploadResult = that.data.uploadResult;
            uploadResult[index] = 2;
            that.setData({ uploadResult: uploadResult });

            that.check();

            wx.showToast({
              title: '上传图片失败',
              icon: 'loading',
              duration: 2000
            })
          });
        }

      },
      fail: function () {
        // fail
      },
      complete: function () {
        // complete
      }
    })
  }
})