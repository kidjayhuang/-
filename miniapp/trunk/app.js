//app.js
var notify = require("utils/notify.js");
var QQMapWX = require('libs/qqmap-wx-jssdk.js');
var qqmapsdk;

App({
  onLaunch: function () {
    var that = this;
    //先验证微信自身登陆态是否过期
    wx.checkSession({
      success: function (res) {
        console.log('session not expired');
        //从缓存获取user_session
        var user_session = wx.getStorageSync('user_session');
        if (user_session == '') {     //取不到user_session需要登陆
          that.login();
        }
        else {
          that.check(user_session);   //取到user_session需要验证是否有效
        }
      },
      fail: function (finfo) {
        //wx session 到期，需要重新登陆
        that.login()
      }
    })

    // 实例化API核心类
    qqmapsdk = new QQMapWX({
      key: '5IVBZ-HHZK5-GUYI5-Q3676-6Z6X5-GVFD6'
    });
  },
  onShow: function() {
    // Do something when show.
    this.getLocation();
  },
  onHide: function() {
    // Do something when hide.
  },
  onError: function(msg) {
    console.log(msg)
  },
  check: function (session) {
    var that = this;
    wx.request({
      url: 'https://api.sca.programplus.cn/auth/check',
      header: {
        session: session
      },
      success: function (login) {
        console.log(login.data)
        //过期了，重新登陆
        if (login.data.data.expire == 0) {
          that.login()
        } else {
          that.globalData.auth = 1;
          notify.postNotification("auth", 1);
        }
      }
    })
  },
  login: function (callback) {
    var that = this;
    console.log('begin login ...')
    //调用登陆接口
    wx.login({
      success: function (res) {
        //直接从微信获取用户信息，基础信息如昵称、性别、头像等小程序直接使用，其它信息发送给后台换取session_key以及敏感信息openid和unionid.      
        wx.getUserInfo({
          success: function (user) {
            console.log(user);
            var user_info = user.userInfo;
            that.globalData.userInfo = user_info;
            console.log('get userinfo success. user is ' + user_info.nickName);
            //发送给登陆后台，获取user_session
            wx.request({
              url: 'https://api.sca.programplus.cn/auth/login',
              header: {
                code: res.code,
                rawData: encodeURI(user.rawData),
                signature: user.signature,
                encryptedData: user.encryptedData,
                iv: user.iv
              },
              success: function (login) {
                console.log(login.data)
                if (login.data.code == 0) {
                  wx.setStorageSync("user_session", login.data.data.session);
                  that.globalData.auth = 1;
                  notify.postNotification("auth", 1);
                  if (callback) {
                    callback();
                  }
                }
              }
            })
          },
          fail: function (res) {
            console.log("refuse getuserinfo============");
            console.log(res);
            that.globalData.auth = 2;
            notify.postNotification("auth", 2);
          }
        })
      }
    })
  },
  addCropPhotoListener: function (callback) {
    this.cropPhotoListener = callback;
    console.log("add listener");
  },
  setCropPhoto: function (cropPhoto) {
    console.log("set crop photo");
    this.globalData.cropPhoto = cropPhoto;
    if (this.cropPhotoListener != null) {
      console.log("call back listener");
      this.cropPhotoListener(cropPhoto);
    }
  },
  getLocation: function () {
    var that = this;
    wx.getLocation({
      type: 'gcj02',
      success: function(res) {
        var latitude = res.latitude
        var longitude = res.longitude
        that.globalData.location.latitude = latitude;
        that.globalData.location.longitude = longitude;

        qqmapsdk.reverseGeocoder({
          location: {
                latitude: latitude,
                longitude: longitude
            },
            success: function(res) {
                console.log(res);
                var address = res.result.address;
                that.globalData.location.address = address;
                that.globalData.location.name = "";

                that.globalData.defaultLocation.latitude = latitude;
                that.globalData.defaultLocation.longitude = longitude;
                that.globalData.defaultLocation.address = address;
                that.globalData.defaultLocation.name = "";

                notify.postNotification("location", that.globalData.location);
            },
            fail: function(res) {
                console.log(res);
            },
            complete: function(res) {
                console.log(res);
            }
        });
      }
    })
  },
  globalData: {
    auth: 0,
    userInfo: null,
    cache: {
      expire: 600000, // 十分钟缓存
    },
    tempPhoto: "",
    cropPhoto: "",
    location: {
      name: "深圳市软件产业基地",
      address: "广东省深圳市南山区学府路",
      longitude: 113.93822,
      latitude: 22.52388
    },
    defaultLocation: {
      name: "深圳市软件产业基地",
      address: "广东省深圳市南山区学府路",
      longitude: 113.93822,
      latitude: 22.52388
    }
  }
})