(()=>{var e={155:(e,t,r)=>{"use strict";r.r(t),r.d(t,{default:()=>a});var n=r(645),o=r.n(n)()((function(e){return e[1]}));o.push([e.id,"\n.field-name[data-v-93ddba46] {\n    font-weight: 600;\n    --tw-text-opacity: 1;\n    color: rgb(49 46 129 / var(--tw-text-opacity))\n}\n.field-name[data-v-93ddba46]:is(.dark *) {\n    --tw-text-opacity: 1;\n    color: rgb(165 180 252 / var(--tw-text-opacity))\n}\n",""]);const a=o},645:e=>{"use strict";e.exports=function(e){var t=[];return t.toString=function(){return this.map((function(t){var r=e(t);return t[2]?"@media ".concat(t[2]," {").concat(r,"}"):r})).join("")},t.i=function(e,r,n){"string"==typeof e&&(e=[[null,e,""]]);var o={};if(n)for(var a=0;a<this.length;a++){var s=this[a][0];null!=s&&(o[s]=!0)}for(var i=0;i<e.length;i++){var d=[].concat(e[i]);n&&o[d[0]]||(r&&(d[2]?d[2]="".concat(r," and ").concat(d[2]):d[2]=r),t.push(d))}},t}},744:(e,t)=>{"use strict";t.Z=(e,t)=>{const r=e.__vccOpts||e;for(const[e,n]of t)r[e]=n;return r}},328:(e,t,r)=>{var n=r(155);n.__esModule&&(n=n.default),"string"==typeof n&&(n=[[e.id,n,""]]),n.locals&&(e.exports=n.locals);(0,r(346).Z)("5b8eb486",n,!0,{})},346:(e,t,r)=>{"use strict";function n(e,t){for(var r=[],n={},o=0;o<t.length;o++){var a=t[o],s=a[0],i={id:e+":"+o,css:a[1],media:a[2],sourceMap:a[3]};n[s]?n[s].parts.push(i):r.push(n[s]={id:s,parts:[i]})}return r}r.d(t,{Z:()=>v});var o="undefined"!=typeof document;if("undefined"!=typeof DEBUG&&DEBUG&&!o)throw new Error("vue-style-loader cannot be used in a non-browser environment. Use { target: 'node' } in your Webpack config to indicate a server-rendering environment.");var a={},s=o&&(document.head||document.getElementsByTagName("head")[0]),i=null,d=0,c=!1,l=function(){},u=null,f="data-vue-ssr-id",p="undefined"!=typeof navigator&&/msie [6-9]\b/.test(navigator.userAgent.toLowerCase());function v(e,t,r,o){c=r,u=o||{};var s=n(e,t);return h(s),function(t){for(var r=[],o=0;o<s.length;o++){var i=s[o];(d=a[i.id]).refs--,r.push(d)}t?h(s=n(e,t)):s=[];for(o=0;o<r.length;o++){var d;if(0===(d=r[o]).refs){for(var c=0;c<d.parts.length;c++)d.parts[c]();delete a[d.id]}}}}function h(e){for(var t=0;t<e.length;t++){var r=e[t],n=a[r.id];if(n){n.refs++;for(var o=0;o<n.parts.length;o++)n.parts[o](r.parts[o]);for(;o<r.parts.length;o++)n.parts.push(g(r.parts[o]));n.parts.length>r.parts.length&&(n.parts.length=r.parts.length)}else{var s=[];for(o=0;o<r.parts.length;o++)s.push(g(r.parts[o]));a[r.id]={id:r.id,refs:1,parts:s}}}}function m(){var e=document.createElement("style");return e.type="text/css",s.appendChild(e),e}function g(e){var t,r,n=document.querySelector("style["+f+'~="'+e.id+'"]');if(n){if(c)return l;n.parentNode.removeChild(n)}if(p){var o=d++;n=i||(i=m()),t=x.bind(null,n,o,!1),r=x.bind(null,n,o,!0)}else n=m(),t=N.bind(null,n),r=function(){n.parentNode.removeChild(n)};return t(e),function(n){if(n){if(n.css===e.css&&n.media===e.media&&n.sourceMap===e.sourceMap)return;t(e=n)}else r()}}var b,y=(b=[],function(e,t){return b[e]=t,b.filter(Boolean).join("\n")});function x(e,t,r,n){var o=r?"":n.css;if(e.styleSheet)e.styleSheet.cssText=y(t,o);else{var a=document.createTextNode(o),s=e.childNodes;s[t]&&e.removeChild(s[t]),s.length?e.insertBefore(a,s[t]):e.appendChild(a)}}function N(e,t){var r=t.css,n=t.media,o=t.sourceMap;if(n&&e.setAttribute("media",n),u.ssrId&&e.setAttribute(f,t.id),o&&(r+="\n/*# sourceURL="+o.sources[0]+" */",r+="\n/*# sourceMappingURL=data:application/json;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(o))))+" */"),e.styleSheet)e.styleSheet.cssText=r;else{for(;e.firstChild;)e.removeChild(e.firstChild);e.appendChild(document.createTextNode(r))}}}},t={};function r(n){var o=t[n];if(void 0!==o)return o.exports;var a=t[n]={id:n,exports:{}};return e[n](a,a.exports,r),a.exports}r.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return r.d(t,{a:t}),t},r.d=(e,t)=>{for(var n in t)r.o(t,n)&&!r.o(e,n)&&Object.defineProperty(e,n,{enumerable:!0,get:t[n]})},r.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),r.r=e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},(()=>{"use strict";const e=Vue;var t={class:"flex flex-col md:flex-row -mx-6 px-6 break-all lg:break-words"},n={class:"field-name"};const o={props:["index","resourceName","resourceId","resource","panel"],data:function(){return{fieldName:null}},mounted:function(){this.fieldName=Object.values(this.resource.fields),find((function(e){return"name"==e.attribute})).value}};r(328);const a=(0,r(744).Z)(o,[["render",function(r,o,a,s,i,d){return(0,e.openBlock)(),(0,e.createElementBlock)("div",t,[(0,e.createElementVNode)("p",null,[(0,e.createTextVNode)(" Resource Tool for "),(0,e.createElementVNode)("span",n,(0,e.toDisplayString)(i.fieldName),1)])])}],["__scopeId","data-v-93ddba46"]]);Nova.booting((function(e,t){e.component("resource-tool",a)}))})()})();