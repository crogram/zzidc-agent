// banner轮播
window.onload = function(){
        var swiper = new Swiper('.swiper-container', {
        pagination: '.swiper-pagination',
        nextButton: '.swiper-button-next',
        prevButton: '.swiper-button-prev',
        paginationClickable: true,
        spaceBetween: 30,
        centeredSlides: true,
        autoplay: 2500,
        autoplayDisableOnInteraction: false
    });}
//电脑版、手机版
var edition_a = document.querySelectorAll('.edition a');
for (var i = edition_a.length - 1; i >= 0; i--) {
    edition_a[i].addEventListener('click',function(){
        for (var j = edition_a.length - 1; j >= 0; j--) {
            edition_a[j].classList.remove('g009');
        }
        this.classList.add('g009');
    })
}
// 滑动模块

var index = 0;
var num = 0;
//添加过渡
function addTransition(dv){
    dv.style.transition = "all 0.2s ease-in";
}
//添加移动事件
function addTranslate(dv,x){
    dv.style.transform = 'translateX('+ x +'px)';
}
// 清除过渡
function removeTransition(dv){
    dv.style.transition = 'none';
}
//清除active
function removeactive(){
    for(var i = 0; i < item.length; i++){
        item[i].classList.remove('solu-active');
    }
}
// 过渡监听兼容
function setTransitionEnd(ele,callback){
    ele.addEventListener('webkitTransitionEnd',function(){
        callback && callback();
    })
    ele.addEventListener('transsitionEnd',function(){
        callback && callback();
    })
}
// touch事件
var start = 0;
var end = 0;
var instance = 0;
// 获取滚动条高度兼容
function getScrollTop() { 
    var scrollTop = 0; 
    if (document.documentElement && document.documentElement.scrollTop) { 
        scrollTop = document.documentElement.scrollTop; 
    }else if (document.body) { 
        scrollTop = document.body.scrollTop; 
    } 
    return scrollTop; 
} 
// 获取非行内样式
function getStyle(attr,value){
    if(window.getComputedStyle){
        return getComputedStyle(attr,null)[value];
    }else{
        return attr.currentStyle[value];
    }
}