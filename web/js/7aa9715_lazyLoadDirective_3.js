containerApp.directive("bnLazySrc",["$window","$document",function(e,d){var c=(function(){var k=[];var g=null;var s=100;var h=$(e);var w=d;var j=w.height();var v=null;var l=2000;var t=false;function n(x){k.push(x);if(!g){q()}if(!t){o()}}function p(y){for(var x=0;x<k.length;x++){if(k[x]===y){k.splice(x,1);break}}if(!k.length){u();m()}}function r(){if(g){return}var x=w.height();if(x===j){return}j=x;q()}function f(){var D=[];var A=[];var E=h.height();var C=h.scrollTop();var z=C;var x=(z+E);for(var y=0;y<k.length;y++){var B=k[y];if(B.isVisible(z,x)){D.push(B)}else{A.push(B)}}for(var y=0;y<D.length;y++){D[y].render()}k=A;u();if(!k.length){m()}}function u(){clearTimeout(g);g=null}function q(){g=setTimeout(f,s)}function o(){t=true;h.on("resize.bnLazySrc",i);h.on("scroll.bnLazySrc",i);v=setInterval(r,l)}function m(){t=false;h.off("resize.bnLazySrc");h.off("scroll.bnLazySrc");clearInterval(v)}function i(){if(!g){q()}}return({addImage:n,removeImage:p})})();function b(h){var k=null;var m=false;var g=null;function f(p,n){if(!h.is(":visible")){return(false)}if(g===null){g=h.height()}var q=h.offset().top;var o=(q+g);return(((q<=n)&&(q>=p))||((o<=n)&&(o>=p))||((q<=p)&&(o>=n)))}function i(){m=true;j()}function l(n){k=n;if(m){j()}}function j(){h[0].src=k}return({isVisible:f,render:i,setSource:l})}function a(g,h,f){var i=new b(h);c.addImage(i);f.$observe("bnLazySrc",function(j){i.setSource(j)});g.$on("$destroy",function(){c.removeImage(i)})}return({link:a,restrict:"A"})}]);