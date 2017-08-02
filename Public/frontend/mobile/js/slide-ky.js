var Slider=function(dom,step,count,option){this.dom=dom;this.step=parseInt(step)||0;this.count=parseInt(count)||0;this.itemIndex=0;this.curDistance=0;this.distance=0;this.preDistance=0;this.curDistance=0;this.handlers={};this.status=Slide_Status.READY;this.enableSwitch=true;this.option=makeOption(option);this._init()};var Slide_Status={READY:1,TOUCH_START:2,TOUCH_MOVE:3,TOUCH_END:4,ANIMATING:5};var Direction={X:"X",Y:"Y"};var doc=document,win=window,undefine;var _vendor="",_prefix="",_vendors=["webkit","ms","o","moz"];var _div=doc.createElement("div");var style=_div.style,name="transform",_name;_name=name.charAt(0).toUpperCase()+name.substr(1);for(var i=0,one;one=_vendors[i];i++){_name=one+_name;if(_name in style){_prefix=one;break}}if(!(name in style)){_vendor=_prefix}var _requestAnimationFrame=(function(){return win.requestAnimationFrame||win.webkitRequestAnimationFrame||win.mozRequestAnimationFrame||win.oRequestAnimationFrame||win.msRequestAnimationFrame||function(callback){win.setTimeout(callback,1000/60)}})();var makeOption=function(option){var defaultOption={transformProperty:"translateX",nextRatio:2/5,speedThreshold:4,disableBoundaryMove:false,boundaryAnimationName:"",disableTouch:false,touchArea:doc.documentElement,autoAnimationDuration:200,slideRatio:2/3,lockOnAnimating:false,preventDefault:"auto"};if(option){(option.transformProperty!==undefine)&&(defaultOption.transformProperty=option.transformProperty);option.disableTouch!==undefine&&(defaultOption.disableTouch=option.disableTouch);option.touchArea!==undefine&&(defaultOption.touchArea=option.touchArea);option.disableBoundaryMove!==undefine&&(defaultOption.disableBoundaryMove=option.disableBoundaryMove);option.boundaryAnimationName!==undefine&&(defaultOption.boundaryAnimationName=option.boundaryAnimationName);option.nextRatio!==undefine&&(defaultOption.nextRatio=option.nextRatio);option.speedThreshold!==undefine&&(defaultOption.speedThreshold=option.speedThreshold);option.autoAnimationDuration!==undefine&&(defaultOption.autoAnimationDuration=option.autoAnimationDuration);option.slideRatio!==undefine&&(defaultOption.slideRatio=option.slideRatio);option.lockOnAnimating!==undefine&&(defaultOption.lockOnAnimating=option.lockOnAnimating);option.preventDefault!==undefine&&(defaultOption.preventDefault=option.preventDefault)}return defaultOption};Slider.prototype={link:function(otherSlider){if(otherSlider instanceof Slider){this.on("touchmove",function(e,distance){otherSlider.move(distance)});this.on("locate",function(itemIndex){otherSlider.locate(itemIndex)});this.on("animationinterrupt",function(){otherSlider.animationInterrupt()})}},locate:function(){var useAnimation=true,itemIndex,callback,i=0;if(typeof arguments[0]=="boolean"){useAnimation=arguments[0];i++}itemIndex=arguments[i];callback=arguments[i+1];if(itemIndex===undefine){itemIndex=this.itemIndex}itemIndex=parseInt(itemIndex)||0;if(!this._isCircleLayout){(itemIndex<0)&&(itemIndex=0);(itemIndex>this.count-1)&&(itemIndex=this.count-1)}this.lastDistance=this.curDistance;this.curDistance=0-this.step*itemIndex;if(useAnimation){var me=this;this._animate(this._makeTransformValue(this.curDistance),function(){callback&&callback(itemIndex);me._emit("locateEnd",[itemIndex])})}else{this._transform(this._makeTransformValue(this.curDistance))}this.itemIndex=itemIndex;this._emit("locate",[itemIndex])},next:function(){this.locate(this.itemIndex+1)},prev:function(){this.locate(this.itemIndex-1)},disable:function(){this.enableSwitch=false},enable:function(){this.enableSwitch=true},move:function(distanceOrRatio){var distance=0,absDistanceOrRatio;distanceOrRatio=parseFloat(distanceOrRatio);absDistanceOrRatio=Math.abs(distanceOrRatio);if(absDistanceOrRatio>=1){distance=distanceOrRatio}else{if(absDistanceOrRatio>0){distance=parseInt(this.step*distanceOrRatio)||0}}if(distance){this._move(function(){this._transform(this._makeTransformValue(this.curDistance+distance/2));distance=undefine})}},updateStep:function(step){step=parseInt(step);if(step){this.step=step;this.locate(false)}},animationInterrupt:function(){var curDistance=this._getCurrentDistance();this._transform(this._makeTransformValue(curDistance));this._animateEnd();return curDistance},on:function(eventName,fn){this.handlers[eventName]||(this.handlers[eventName]=[]);this.handlers[eventName].push(fn)},_emit:function(eventName,params){var handlers=this.handlers[eventName];if(handlers&&handlers.length){for(var i=0,one;one=handlers[i];i++){one.apply&&one.apply(this,params||[])}}},_init:function(){this._transform({"translate3d":"0,0,0"});this._initTransformProperty();if(!this.option.disableTouch){this._initAnimation()}var me=this,touchArea=this.option.touchArea;var setAreaDistance=function(){me.areaDistance=me.direction==Direction.Y?touchArea.offsetHeight:touchArea.offsetWidth;if(!me.areaDistance){me.areaDistance=me.direction==Direction.Y?win.innerHeight:win.innerWidth}};win.addEventListener("resize",setAreaDistance,false);setAreaDistance()},_initAnimation:function(){var me=this,touchArea=this.option.touchArea;
if(win.navigator.msPointerEnabled){touchArea.addEventListener("MSPointerDown",function(e){if(e.pointerType==e.MSPOINTER_TYPE_TOUCH){me._touchStart(e,e)}},false);touchArea.addEventListener("MSPointerMove",function(e){if(e.pointerType==e.MSPOINTER_TYPE_TOUCH){me._touchMove(e,e)}},false);touchArea.addEventListener("MSPointerUp",function(e){if(e.pointerType==e.MSPOINTER_TYPE_TOUCH){me._touchEnd(e)}},false)}else{touchArea.addEventListener("touchstart",function(e){me._touchStart(e,e.targetTouches[0])},false);touchArea.addEventListener("touchmove",function(e){me._touchMove(e,e.targetTouches[0])},false);touchArea.addEventListener("touchend",function(e){me._touchEnd(e)},false)}},_initTransformProperty:function(){var transformProperty=this.option.transformProperty,transformValueTmpl,_getValueFromMatrix,direction=Direction.X,isCircleLayout=false;var me=this;var parseDeg=function(baseDeg){var halfDeg=(me.curDistance+me.lastDistance)/2;var baseHalfDegMod=halfDeg%360;if(baseHalfDegMod>180){baseHalfDegMod-=360}else{if(baseHalfDegMod<-180){baseHalfDegMod+=360}}var degSpan=baseDeg-baseHalfDegMod;return halfDeg+degSpan};switch(transformProperty){default:case"translateX":transformProperty="translate3d";transformValueTmpl="{val}px,0,0";_getValueFromMatrix=function(matrixUnits){if(matrixUnits.length==6){return parseInt(matrixUnits[4])}else{if(matrixUnits.length==16){return parseInt(matrixUnits[12])}}};break;case"translateY":transformProperty="translate3d";transformValueTmpl="0,{val}px,0";direction=Direction.Y;_getValueFromMatrix=function(matrixUnits){if(matrixUnits.length==6){return parseInt(matrixUnits[5])}else{if(matrixUnits.length==16){return parseInt(matrixUnits[13])}}};break;case"translateZ":transformProperty="translate3d";transformValueTmpl="0,0,{val}px";_getValueFromMatrix=function(matrixUnits){return parseInt(matrixUnits[14])};break;case"rotateX":transformValueTmpl="{val}deg";direction=Direction.Y;isCircleLayout=true;_getValueFromMatrix=function(matrixUnits){var cosVal=parseFloat(matrixUnits[5]).toFixed(2);var sinVal=parseFloat(matrixUnits[6]);var cosDeg=Math.acos(cosVal)/Math.PI*180;var val=sinVal>=0?cosDeg:0-cosDeg;return parseDeg(val)};break;case"rotateY":transformValueTmpl="{val}deg";isCircleLayout=true;_getValueFromMatrix=function(matrixUnits){var cosVal=parseFloat(matrixUnits[0]).toFixed(2);var sinVal=parseFloat(matrixUnits[8]);var cosDeg=Math.acos(cosVal)/Math.PI*180;var val=sinVal>=0?cosDeg:0-cosDeg;return parseDeg(val)};break;case"rotateZ":transformValueTmpl="{val}deg";isCircleLayout=true;_getValueFromMatrix=function(matrixUnits){var cosVal=0,sinVal=0;if(matrixUnits.length==6){cosVal=parseFloat(matrixUnits[0]).toFixed(2);sinVal=parseFloat(matrixUnits[1])}else{if(matrixUnits.length==16){}}var cosDeg=Math.acos(cosVal)/Math.PI*180;var val=sinVal>=0?cosDeg:0-cosDeg;return parseDeg(val)};break}this._makeTransformValue=function(distance){var obj={};obj[transformProperty]=transformValueTmpl.replace("{val}",distance);return obj};this._getCurrentDistance=function(){var transformValue=_css(this.dom,"transform");transformValue=transformValue.replace(/^matrix(?:3d)?\((.+)\)$/,"$1");var matrixUnits=transformValue.split(",")||[];return _getValueFromMatrix(matrixUnits)};this.DPI=_getDPI()[direction];this.direction=direction;this._isCircleLayout=isCircleLayout},_transform:function(transformObj){this._curTransformObj||(this._curTransformObj={});for(var key in transformObj){this._curTransformObj[key]=transformObj[key]}var builder=[];for(var ckey in this._curTransformObj){builder.push(ckey,"(",this._curTransformObj[ckey],")")}if(builder.length){_css(this.dom,"transform",builder.join(""))}},_parseDistance:function(oriDistance){return parseInt(parseFloat(oriDistance)/this.areaDistance*this.step)},_touchStart:function(e,toucher){if(!this.enableSwitch){return}var curDistance=this.curDistance;if(this.status==Slide_Status.ANIMATING){if(this.boundaryMode||this.option.lockOnAnimating){return}curDistance=this.animationInterrupt();this._emit("animationinterrupt")}this.oriDistance=0;this.distance=0;this.preDistance=curDistance-this.curDistance;this.status=Slide_Status.TOUCH_START;this.startTime=new Date();this.motionable=true;this.touchDirection=0;this.startPos={X:toucher.pageX,Y:toucher.pageY};this.startValue=this.startPos[this.direction];this._emit("touchstart",[e])},_touchMove:function(e,toucher){if(!this.enableSwitch){return}if(this.option.preventDefault==="auto"){if(!this.touchDirection){var dx=Math.abs(toucher.pageX-this.startPos.X);var dy=Math.abs(toucher.pageY-this.startPos.Y);if(dx!=0||dy!=0){this.touchDirection=dx>dy?Direction.X:Direction.Y}}if(this.touchDirection==this.direction){e.preventDefault()}else{return}}else{if(this.option.preventDefault===true){e.preventDefault()}}if(!this.motionable){return}var endValue=toucher["page"+this.direction];this.oriDistance=endValue-this.startValue;if(this.status!=Slide_Status.TOUCH_START&&this.status!=Slide_Status.TOUCH_MOVE){return}if(!this.oriDistance){return}this.distance=this._parseDistance(this.oriDistance)+this.preDistance/this.option.slideRatio;
this.timeSpan=new Date()-this.startTime;this._move(function(){var doCommonMove=true;if((this.itemIndex===0&&this.distance>0)||(this.itemIndex==this.count-1&&this.distance<0)){if(this.option.disableBoundaryMove){doCommonMove=false}else{var boundaryAnimation=Slider.ext.boundaryAnimation[this.option.boundaryAnimationName];if(boundaryAnimation&&boundaryAnimation.touchMove){this.boundaryMode=true;boundaryAnimation.touchMove.call(this);doCommonMove=false}}}if(doCommonMove){this.boundaryMode=false;if(this.option.slideRatio){this.status=Slide_Status.TOUCH_MOVE;this._transform(this._makeTransformValue(this.curDistance+this.distance*this.option.slideRatio))}}});this._emit("touchmove",[e,this.distance])},_touchEnd:function(e){if(!this.enableSwitch){return}if(!this.motionable){return}this.motionable=false;if(this.boundaryMode){var boundaryAnimation=Slider.ext.boundaryAnimation[this.option.boundaryAnimationName];if(boundaryAnimation&&boundaryAnimation.touchEnd){boundaryAnimation.touchEnd.call(this)}}else{if(this.distance!==0||this.preDistance!==0){var itemIndex=this.itemIndex;if(this.oriDistance){if(Math.abs(this.oriDistance/this.DPI)/this.timeSpan*1000>this.option.speedThreshold||Math.abs(this.oriDistance)>this.areaDistance*this.option.nextRatio){this.oriDistance<0?(itemIndex++):(itemIndex--)}}this.locate(itemIndex)}else{this.status==Slide_Status.READY}}this._emit("touchend",[e])},_move:function(fn){this._curFrameFn=fn;if(this.moveFrameFilled){return}this.moveFrameFilled=true;var me=this;_requestAnimationFrame(function(){me.moveFrameFilled=false;if(me.status!=Slide_Status.ANIMATING){me._curFrameFn&&me._curFrameFn.call(me)}me._curFrameFn=undefine})},_animate:function(destTransformObj,callback){this.status=Slide_Status.ANIMATING;_css(this.dom,"transition",[_formatStyleName("transform")," ",this.option.autoAnimationDuration,"ms ease-out"].join(""));var me=this;var endFn=this.dom.endFn=function(e){if(e&&e.target!==me.dom){return}me._animateEnd();e&&e.stopPropagation();callback&&callback()};this.dom.addEventListener("transitionEnd",endFn,false);this.dom.addEventListener(_getCssEventNameWithVendor("transitionEnd"),endFn,false);this.dom.animationTimer=setTimeout(endFn,this.option.autoAnimationDuration+50);this._transform(destTransformObj)},_animateEnd:function(){this.status=Slide_Status.READY;_css(this.dom,"transition","");this.dom.removeEventListener("transitionEnd",this.dom.endFn,false);this.dom.removeEventListener(_getCssEventNameWithVendor("transitionEnd",true),this.dom.endFn,false);clearTimeout(this.dom.animationTimer);this.dom.endFn=null}};Slider.prototype.constructor=Slider;var _formatStyleName=function(styleName,isCamel){if(_prefix){if(isCamel){return _prefix+styleName.charAt(0).toUpperCase()+styleName.substr(1)}else{return["-",_prefix,"-",styleName].join("")}}else{return styleName}};var _getCssEventNameWithVendor=function(eventName){return _vendor+eventName.charAt(0).toUpperCase()+eventName.substr(1)};var _css=function(dom,styleName,value){if(value===undefine){return doc.defaultView.getComputedStyle(dom,null)[_formatStyleName(styleName,true)]}else{dom.style[_formatStyleName(styleName,true)]=value}};var _getDPI=function(){var xDPI=0,yDPI=0;if(win.screen.deviceXDPI!==undefine){xDPI=win.screen.deviceXDPI;yDPI=win.screen.deviceYDPI}else{_div.style.cssText="width:1in;height:1in;position:absolute;left:0px;top:0px;z-index:99;visibility:hidden";doc.body.appendChild(_div);xDPI=parseInt(_div.offsetWidth);yDPI=parseInt(_div.offsetHeight);_div.parentNode.removeChild(_div)}return{X:xDPI,Y:yDPI}};Slider.ext={};var boundaryAnimation=Slider.ext.boundaryAnimation={};boundaryAnimation.pudding={touchMove:function(){var originPos=Math.abs(this.curDistance)+(this.oriDistance>0?0:this.step);if(this.direction==Direction.X){_css(this.dom,"transformOrigin",originPos+"px 50%");this._transform({scaleX:1+Math.abs(this.distance)/250*0.15})}else{_css(this.dom,"transformOrigin","50% "+originPos+"px");this._transform({scaleY:1+Math.abs(this.distance)/250*0.15})}},touchEnd:function(){var me=this;var animateObj=this.direction==Direction.X?{scaleX:1}:{scaleY:1};this._animate(animateObj,function(){_css(me.dom,"transformOrigin","")})}};