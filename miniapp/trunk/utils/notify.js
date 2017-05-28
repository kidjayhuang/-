/**
 * author: henery
 * organization: programplus.cn
 */

// 观察者数组
var observers = [];

/**
 * addObserver
 * 添加观察者方法
 * 
 * 参数:
 * name: 通知名
 * fn: 对应的通知方法，接受到通知后进行的动作
 * page: 注册对象，指Page对象
 */
function addObserver(name, fn, page) {
    if (name && fn) {
        if (!page) {
            console.log("addObserver Warning: no page will can't remove observer");
        }
        console.log("addObserver:" + name);
        var observer = {
            name: name,
            fn: fn,
            page: page
        };

        observers.push(observer);

    } else {
        console.log("addObserver error: no fn or name");
    }
}

/**
 * removeObserver
 * 移除观察者方法
 * 
 * 参数:
 * name: 已经注册了的通知
 * page: 移除的通知所在的Page对象
 */
function removeObserver(name, page) {
    console.log("removeObserver:" + name);

    for (var i = 0; i < observers.length; i++) {
        var observer = observers[i];
        if (observer.name === name) {
            if (observer.page === page) {
                observers.splice(i, 1);
                return;
            }
        }
    }
}

/**
 * postNotification
 * 发送通知方法
 * 
 * 参数:
 * name: 已经注册了的通知
 * data: 数据
 */
function postNotification(name, data) {
    console.log("postNotification:" + name);
    if (observers.length == 0) {
        console.log("postNotification error: u hadn't add any notice.");
        return;
    }

    for (var i = 0; i < observers.length; i++) {
        var observer = observers[i];
        if (observer.name === name) {
            observer.fn(data);
        }
    }
}

module.exports = {
    addObserver: addObserver,
    removeObserver: removeObserver,
    postNotification: postNotification
}