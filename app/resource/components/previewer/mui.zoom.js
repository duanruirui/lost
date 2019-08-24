!function(A,o){var t=A.className("zoom"),e=A.className("zoom-scroller"),r="."+t,H="."+e,W="pinchstart",X="pinch",Y="pinchend";"ongesturestart"in o&&(W="gesturestart",X="gesturechange",Y="gestureend"),A.Zoom=function(o,t){var m=this;m.options=A.extend(A.Zoom.defaults,t),m.wrapper=m.element=o,m.scroller=o.querySelector(H),m.scrollerStyle=m.scroller&&m.scroller.style,m.zoomer=o.querySelector(r),m.zoomerStyle=m.zoomer&&m.zoomer.style,m.init=function(){A.options.gestureConfig.pinch=!0,A.options.gestureConfig.doubletap=!0,m.initEvents()},m.initEvents=function(o){var t=o?"removeEventListener":"addEventListener",e=m.scroller;e[t](W,m.onPinchstart),e[t](X,m.onPinch),e[t](Y,m.onPinchend),e[t]("touchstart",m.onTouchstart),e[t]("touchmove",m.onTouchMove),e[t]("touchcancel",m.onTouchEnd),e[t]("touchend",m.onTouchEnd),e[t]("drag",function(o){(p||c)&&o.stopPropagation()}),e[t]("doubletap",function(o){m.toggleZoom(o.detail.center)})},m.transition=function(o,t){return t=t||0,o.webkitTransitionDuration=t+"ms",m},m.translate=function(o,t,e){return t=t||0,e=e||0,o.webkitTransform="translate3d("+t+"px,"+e+"px,0px)",m},m.scale=function(o,t){return t=t||1,o.webkitTransform="translate3d(0,0,0) scale("+t+")",m},m.scrollerTransition=function(o){return m.transition(m.scrollerStyle,o)},m.scrollerTransform=function(o,t){return m.translate(m.scrollerStyle,o,t)},m.zoomerTransition=function(o){return m.transition(m.zoomerStyle,o)},m.zoomerTransform=function(o){return m.scale(m.zoomerStyle,o)};var l=1,s=1,n=!1,c=!1;m.onPinchstart=function(o){c=!0},m.onPinch=function(o){n||(m.zoomerTransition(0),n=!0),(l=(o.detail?o.detail.scale:o.scale)*s)>m.options.maxZoom&&(l=m.options.maxZoom-1+Math.pow(l-m.options.maxZoom+1,.5)),l<m.options.minZoom&&(l=m.options.minZoom+1-Math.pow(m.options.minZoom-l+1,.5)),m.zoomerTransform(l)},m.onPinchend=function(o){l=Math.max(Math.min(l,m.options.maxZoom),m.options.minZoom),m.zoomerTransition(m.options.speed).zoomerTransform(l),s=l,n=!1},m.setZoom=function(o){l=s=o,m.scrollerTransition(m.options.speed).scrollerTransform(0,0),m.zoomerTransition(m.options.speed).zoomerTransform(l)},m.toggleZoom=function(o,t){if("number"==typeof o&&(t=o,o=void 0),t=void 0===t?m.options.speed:t,l&&1!==l)l=s=1,m.scrollerTransition(t).scrollerTransform(0,0);else if(l=s=m.options.maxZoom,o){var e=A.offset(m.zoomer),r=e.top,n=e.left,a=(o.x-n)*l,i=(o.y-r)*l;this._cal(),x<=a&&a<=x+h?a=x-a+h/2:a<x?a=x-a+h/2:x+h<a&&(a=x+h-a-h/2),v<=i&&i<=v+u?i=v-i+u/2:i<v?i=v-i+u/2:v+u<i&&(i=v+u-i-u/2),a=Math.min(Math.max(a,g),x),i=Math.min(Math.max(i,M),v),m.scrollerTransition(t).scrollerTransform(a,i)}else m.scrollerTransition(t).scrollerTransform(0,0);m.zoomerTransition(t).zoomerTransform(l)},m._cal=function(){h=m.wrapper.offsetWidth,u=m.wrapper.offsetHeight,z=m.zoomer.offsetWidth,y=m.zoomer.offsetHeight;var o=z*l,t=y*l;g=Math.min(h/2-o/2,0),x=-g,M=Math.min(u/2-t/2,0),v=-M};var h,u,f,p,d,T,g,M,x,v,z,y,a,i,w,Z,b,S,E,P={},k={};return m.onTouchstart=function(o){o.preventDefault(),f=!0,P.x="touchstart"===o.type?o.targetTouches[0].pageX:o.pageX,P.y="touchstart"===o.type?o.targetTouches[0].pageY:o.pageY},m.onTouchMove=function(o){if(o.preventDefault(),f){if(!p){h=m.wrapper.offsetWidth,u=m.wrapper.offsetHeight,z=m.zoomer.offsetWidth,y=m.zoomer.offsetHeight;var t=A.parseTranslateMatrix(A.getStyles(m.scroller,"webkitTransform"));a=t.x||0,i=t.y||0,m.scrollerTransition(0)}var e=z*l,r=y*l;e<h&&r<u||(g=Math.min(h/2-e/2,0),x=-g,M=Math.min(u/2-r/2,0),v=-M,k.x="touchmove"===o.type?o.targetTouches[0].pageX:o.pageX,k.y="touchmove"===o.type?o.targetTouches[0].pageY:o.pageY,p||n||!(Math.floor(g)===Math.floor(a)&&k.x<P.x||Math.floor(x)===Math.floor(a)&&k.x>P.x)?(p=!0,d=k.x-P.x+a,T=k.y-P.y+i,d<g&&(d=g+1-Math.pow(g-d+1,.8)),x<d&&(d=x-1+Math.pow(d-x+1,.8)),T<M&&(T=M+1-Math.pow(M-T+1,.8)),v<T&&(T=v-1+Math.pow(T-v+1,.8)),w||(w=k.x),S||(S=k.y),Z||(Z=A.now()),b=(k.x-w)/(A.now()-Z)/2,E=(k.y-S)/(A.now()-Z)/2,Math.abs(k.x-w)<2&&(b=0),Math.abs(k.y-S)<2&&(E=0),w=k.x,S=k.y,Z=A.now(),m.scrollerTransform(d,T)):f=!1)}},m.onTouchEnd=function(o){if(o.touches.length||(c=!1),f&&p){p=f=!1;var t=300,e=300,r=d+b*t,n=T+E*e;0!==b&&(t=Math.abs((r-d)/b)),0!==E&&(e=Math.abs((n-T)/E));var a=Math.max(t,e);d=r,T=n;var i=z*l,s=y*l;g=Math.min(h/2-i/2,0),x=-g,M=Math.min(u/2-s/2,0),v=-M,d=Math.max(Math.min(d,x),g),T=Math.max(Math.min(T,v),M),m.scrollerTransition(a).scrollerTransform(d,T)}else p=f=!1},m.destory=function(){m.initEvents(!0),delete A.data[m.wrapper.getAttribute("data-zoomer")],m.wrapper.setAttribute("data-zoomer","")},m.init(),m},A.Zoom.defaults={speed:300,maxZoom:3,minZoom:1},A.fn.zoom=function(e){var r=[];return this.each(function(){var o=null,t=this.getAttribute("data-zoomer");t?o=A.data[t]:(t=++A.uuid,A.data[t]=o=new A.Zoom(this,e),this.setAttribute("data-zoomer",t)),r.push(o)}),1===r.length?r[0]:r}}(mui,window);