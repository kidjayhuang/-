var cache = require("cache.js");
var md5 = require("md5.js");

function getSession() {
    var session = wx.getStorageSync('user_session');
    // session = "SESSION_ef55add660a82a87e290e5e15101dca6";
    return session;
}

function initData(page, url, postData, dataKey) {
    var urlString = cache.getUrlString(url, postData);
    var key = md5.hex_md5(urlString);
    var hasCache = cache.checkCache(key);
    var hasExpire = cache.checkExpire(key);

    if (hasCache) {
        var cacheData = wx.getStorageSync(key);
        var data = cacheData.data;
        var temp = {};
        temp[dataKey] = data;
        page.setData(temp);
        if (hasExpire) {
            loadData(page, url, postData, dataKey);
        }
    } else {
        wx.showToast({
            title: '加载中',
            icon: 'loading',
            duration: 2000
        });
        loadData(page, url, postData, dataKey);
    }
}

function loadData(page, url, postData, dataKey) {
    var urlString = cache.getUrlString(url, postData);
    var key = md5.hex_md5(urlString);
    var that = page;
    var session = getSession();
    var header = {
        session: session,
        "content-type": "application/x-www-form-urlencoded"
    };

    wx.request({
        url: url,
        data: postData,
        header: header,
        method: 'POST',
        success: function (res) {
            var statusCode = res.statusCode;
            if (statusCode == 200) {
                var errorCode = res.data.code;
                var errorMsg = res.data.msg;
                if (errorCode == 0) {
                    var data = res.data.data;
                    var tempData = {};
                    tempData[dataKey] = data;
                    that.setData(tempData);
                    var appInstance = getApp();
                    var expire = appInstance.globalData.cache.expire;
                    var cacheData = {
                        expire: Date.now() + expire,
                        data: data
                    }
                    cache.setStorageSync(key, cacheData);
                    setTimeout(function () {
                        wx.hideToast()
                    }, 1000);
                } else if(errorCode == 100002) {
                    var app = getApp();
                    app.login(function() {
                        loadData(that, url, postData, key, dataKey);
                    });
                } else {
                    wx.showToast({
                        title: errorMsg,
                        duration: 2000
                    });
                }
            } else {
                wx.showToast({
                    title: "数据加载失败",
                    duration: 2000
                });
            }

        },
        fail: function (res) {
            wx.showToast({
                title: "数据加载失败",
                duration: 2000
            });
        }
    });
}

function refreshData(page, url, postData, dataKey) {
    var urlString = cache.getUrlString(url, postData);
    var key = md5.hex_md5(urlString);
    var hasCache = cache.checkCache(key);
    var hasExpire = cache.checkExpire(key);

    if (hasCache) {
        var cacheData = wx.getStorageSync(key);
        var data = cacheData.data;
        if (hasExpire) {
            loadData(page, url, postData, dataKey);
        }
    } else {
        wx.showToast({
            title: '加载中',
            icon: 'loading',
            duration: 2000
        });
        loadData(page, url, postData, dataKey);
    }
}


function initDataWithCallback(page, url, postData, dataKey, success, fail) {
    var urlString = cache.getUrlString(url, postData);
    var key = md5.hex_md5(urlString);
    var hasCache = cache.checkCache(key);
    var hasExpire = cache.checkExpire(key);

    if (hasCache) {
        var cacheData = wx.getStorageSync(key);
        var data = cacheData.data;
        if (dataKey != "") {
            var temp = {};
            temp[dataKey] = data;
            page.setData(temp);
        }
        if (hasExpire) {
            loadDataWithCallback(page, url, postData, dataKey, success, fail);
        } else {
            success(data);
        }
    } else {
        wx.showToast({
            title: '加载中',
            icon: 'loading',
            duration: 2000
        });
        loadDataWithCallback(page, url, postData, dataKey, success, fail);
    }
}

function loadDataWithCallback(page, url, postData, dataKey, success, fail) {
    var urlString = cache.getUrlString(url, postData);
    var key = md5.hex_md5(urlString);
    var that = page;
    var session = getSession();
    var header = {
        session: session,
        "content-type": "application/x-www-form-urlencoded"
    };

    wx.request({
        url: url,
        data: postData,
        header: header,
        method: 'POST',
        success: function (res) {
            var statusCode = res.statusCode;
            if (statusCode == 200) {
                var errorCode = res.data.code;
                var errorMsg = res.data.msg;
                if (errorCode == 0) {
                    var data = res.data.data;
                    if (dataKey != "") {
                        var tempData = {};
                        tempData[dataKey] = data;
                        that.setData(tempData);
                    }
                    
                    var appInstance = getApp();
                    var expire = appInstance.globalData.cache.expire;
                    var cacheData = {
                        expire: Date.now() + expire,
                        data: data
                    }
                    cache.setStorageSync(key, cacheData);
                    setTimeout(function () {
                        wx.hideToast()
                    }, 1000);

                    success(data, true);
                } else if(errorCode == 100002) {
                    var app = getApp();
                    app.login(function() {
                        loadDataWithCallback(that, url, postData, dataKey, success, fail);
                    });
                } else {
                    wx.showToast({
                        title: errorMsg,
                        duration: 2000
                    });
                    fail();
                }
            } else {
                wx.showToast({
                    title: "数据加载失败",
                    duration: 2000
                });
                fail();
            }

        },
        fail: function (res) {
            wx.showToast({
                title: "数据加载失败",
                duration: 2000
            });
            fail();
        }
    });
}

function refreshDataWithCallback(page, url, postData, dataKey, success, fail) {
    var urlString = cache.getUrlString(url, postData);
    var key = md5.hex_md5(urlString);
    var hasCache = cache.checkCache(key);
    var hasExpire = cache.checkExpire(key);

    if (hasCache) {
        var cacheData = wx.getStorageSync(key);
        var data = cacheData.data;
        if (hasExpire) {
            loadDataWithCallback(page, url, postData, dataKey, success, fail);
        } else {
            success(data, false);
        }
    } else {
        wx.showToast({
            title: '加载中',
            icon: 'loading',
            duration: 2000
        });
        loadDataWithCallback(page, url, postData, dataKey, success, fail);
    }
}

function loadNoCacheDataWithCallback(page, url, postData, dataKey, success, fail) {
    var that = page;
    var session = getSession();
    var header = {
        session: session,
        "content-type": "application/x-www-form-urlencoded"
    };

    wx.request({
        url: url,
        data: postData,
        header: header,
        method: 'POST',
        success: function (res) {
            var statusCode = res.statusCode;
            if (statusCode == 200) {
                var errorCode = res.data.code;
                var errorMsg = res.data.msg;
                if (errorCode == 0) {
                    var data = res.data.data;
                    var tempData = {};
                    tempData[dataKey] = data;
                    that.setData(tempData);

                    setTimeout(function () {
                        wx.hideToast()
                    }, 1000);

                    success(data);
                } else if(errorCode == 100002) {
                    var app = getApp();
                    app.login(function() {
                        loadNoCacheDataWithCallback(that, url, postData, dataKey, success, fail);
                    });
                } else {
                    wx.showToast({
                        title: errorMsg,
                        duration: 2000
                    });

                    fail();
                }
            } else {
                wx.showToast({
                    title: "数据加载失败",
                    duration: 2000
                });

                fail();
            }

        },
        fail: function (res) {
            wx.showToast({
                title: "数据加载失败",
                duration: 2000
            });

            fail();
        }
    });
}

function request(page, url, postData, success, fail) {
    var that = page;
    var session = getSession();
    var header = {
        session: session,
        "content-type": "application/x-www-form-urlencoded"
    };

    wx.request({
        url: url,
        data: postData,
        header: header,
        method: 'POST',
        success: function (res) {
            var statusCode = res.statusCode;
            if (statusCode == 200) {
                var errorCode = res.data.code;
                var errorMsg = res.data.msg;
                if (errorCode == 0) {
                    var data = res.data.data;
                    setTimeout(function () {
                        wx.hideToast()
                    }, 1000);
                    success(data);
                } else if(errorCode == 100002) {
                    var app = getApp();
                    console.log("re login ----------");
                    app.login(function() {
                        request(that, url, postData, success, fail);
                    });
                } else {
                    wx.showToast({
                        title: errorMsg,
                        duration: 2000
                    });
                    fail();
                }
            } else {
                wx.showToast({
                    title: "数据加载失败",
                    duration: 2000
                });
                fail();
            }
        },
        fail: function (res) {
            wx.showToast({
                title: "数据加载失败",
                duration: 2000
            });
            fail();
        }
    });
}

function getUploadSign(success, fail) {
    var url = "https://imgapi.programplus.cn/cos/sig";
    var urlString = cache.getUrlString(url, {});
    var key = md5.hex_md5(urlString);
    var hasCache = cache.checkCache(key);
    var hasExpire = cache.checkExpire(key);

    if (hasExpire) {
        var session = getSession();
        var header = {
            session: session,
            "content-type": "application/x-www-form-urlencoded"
        };
        wx.request({
            url: url,
            data: {},
            header: header,
            method: 'POST',
            success: function (res) {
                var statusCode = res.statusCode;
                if (statusCode == 200) {
                    var errorCode = res.data.code;
                    var errorMsg = res.data.msg;
                    if (errorCode == 0) {
                        var data = res.data.data;
                        var sign = data.sig;
                        var expired = data.expired;
                        var appInstance = getApp();
                        var cacheData = {
                            expire: expired * 1000,
                            data: sign
                        }
                        cache.setStorageSync(key, cacheData);
                        success(sign);
                    } else {
                        fail();
                    }
                } else {
                    fail();
                }

            },
            fail: function (res) {
                fail();
            }
        });

    } else {
        var cacheData = wx.getStorageSync(key);
        var sign = cacheData.data;
        success(sign);
    }
}

function upload(page, tempFilePath, sign, success, fail) {
    var filename = tempFilePath.substr(9);
    var appid = 1252842507;
    var bucket = "pic";
    var folder = "sca";
    var url = "https://gz.file.myqcloud.com/files/v2/";
    var sign = encodeURIComponent(sign);
    var uploadurl = url + appid + "/" + bucket + "/" + folder + "/" + encodeURI(filename) + "?sign=" + sign;

    wx.uploadFile({
        url: uploadurl, //仅为示例，非真实的接口地址
        filePath: tempFilePath,
        name: 'fileContent',
        formData: {
            op: "upload",
            insertOnly: 1
        },
        success: function (res) {
            var json = JSON.parse(res.data);
            var code = json.code;

            if (code == 0) {
                console.log("upload success")
                console.log(json);
                console.log("download_url" + json.data.access_url);
                var accessUrl = json.data.access_url;
                var imageUrl = "http://picimg.programplus.cn" + accessUrl.substr(accessUrl.indexOf(".com") + 4);
                success(imageUrl);
            } else {
                fail();
            }
        },
        fail: function (res) {
            console.log("upload fail")
            fail();
        },
        complete: function (res) {
            console.log("upload complete")
        }
    })
}

function uploads(page, index, tempFilePath, sign, success, fail) {
    var filename = tempFilePath.substr(9);
    var appid = 1252842507;
    var bucket = "pic";
    var folder = "sca";
    var url = "https://gz.file.myqcloud.com/files/v2/";
    var sign = encodeURIComponent(sign);
    var uploadurl = url + appid + "/" + bucket + "/" + folder + "/" + encodeURI(filename) + "?sign=" + sign;

    wx.uploadFile({
        url: uploadurl, //仅为示例，非真实的接口地址
        filePath: tempFilePath,
        name: 'fileContent',
        formData: {
            op: "upload",
            insertOnly: 1
        },
        success: function (res) {
            var json = JSON.parse(res.data);
            var code = json.code;

            if (code == 0) {
                console.log("upload success")
                console.log(json);
                console.log("download_url" + json.data.access_url);
                var accessUrl = json.data.access_url;
                var imageUrl = "http://picimg.programplus.cn" + accessUrl.substr(accessUrl.indexOf(".com") + 4);
                success(index, imageUrl);
            } else {
                fail(index);
            }
        },
        fail: function (res) {
            console.log("upload fail")
            fail(index);
        },
        complete: function (res) {
            console.log("upload complete")
        }
    })
}

function setExpire(url, postData) {
    var urlString = cache.getUrlString(url, postData);
    var key = md5.hex_md5(urlString);
    cache.setExpire(key);
}

function updateStorage(url, postData, data) {
    var urlString = cache.getUrlString(url, postData);
    var key = md5.hex_md5(urlString);
    cache.setStorageSync(key, data);
}

module.exports = {
    initData: initData,
    loadData: loadData,
    refreshData: refreshData,
    initDataWithCallback: initDataWithCallback,
    loadDataWithCallback: loadDataWithCallback,
    refreshDataWithCallback: refreshDataWithCallback,
    getSession: getSession,
    loadNoCacheDataWithCallback: loadNoCacheDataWithCallback,
    request: request,
    getUploadSign: getUploadSign,
    upload: upload,
    uploads: uploads,
    setExpire: setExpire,
    updateStorage: updateStorage
}