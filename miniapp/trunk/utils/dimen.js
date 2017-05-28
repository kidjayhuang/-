var width = 375;
var r = 0.5;

function init(w) {
    width = w;
    r = width / 750.0;
}

function px2rpx(px) {
    var rpx = px / r;
    return rpx;
}

function rpx2px(rpx) {
    var px = rpx * r;
    return px;
}

module.exports = {
    init: init,
    px2rpx: px2rpx,
    rpx2px: rpx2px
}