

"use strict";

function isResponsive(width){
	return jQuery(window).width() <= parseInt(width);
}

function isUserAgent(type){
	return navigator.userAgent.toLowerCase().indexOf(type.toLowerCase()) > -1;
}

function isMobile(){
	return isUserAgent('android') || isUserAgent('iphone') || isUserAgent('ipad') || isUserAgent('ipod');
}

function isAndroid(){
	return isUserAgent('android');
}

function isIpad(){
	return isUserAgent('ipad');
}

