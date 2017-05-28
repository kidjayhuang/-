function checkCache(key) {
    var data = wx.getStorageSync(key);
    if (data) {
        return true;
    } else {
        return false;
    }
}

function checkExpire(key) {
    var data = wx.getStorageSync(key);
    if (data) {
        if (data.expire < Date.now()) {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

function getUrlString(url, postValue) {
    var urlString = url;
    if (postValue) {
        for (var key in postValue) {
            urlString += "&" + key + "=" + postValue[key];
        }
    }
    return urlString;
}

function setExpire(key) {
    var data = wx.getStorageSync(key);
    if (data) {
        data.expire = Date.now();
        wx.setStorageSync(key, data);
    }
}

function setStorageSync(key, value) {
    try {
        wx.setStorageSync(key, value);
    } catch (e) {
        var currentSize = 0;
        var limitSize = 10240;
        var res = wx.getStorageInfoSync();
        currentSize = res.currentSize;
        limitSize = res.limitSize;
       
        console.log("cache size:" + currentSize);
        console.log(e);
        // if (currentSize > 10) {
        clearStorage();
        // }
        wx.setStorageSync(key, value);
    }
}

function clearStorage() {
    var res = wx.getStorageInfoSync();

    var session = wx.getStorageSync("user_session");
    var sign = wx.getStorageSync("72c5fbe44b8d11fe48e31355042b5351");
    
    wx.clearStorageSync();

    if (session) {
        wx.setStorageSync("user_session", session);
    }

    if (sign) {
        wx.setStorageSync("72c5fbe44b8d11fe48e31355042b5351", sign);
    }
}

module.exports = {
    checkCache: checkCache,
    checkExpire: checkExpire,
    getUrlString: getUrlString,
    setExpire: setExpire,
    setStorageSync: setStorageSync
}