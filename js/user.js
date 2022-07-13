/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./dev/js/user.js":
/*!************************!*\
  !*** ./dev/js/user.js ***!
  \************************/
/***/ (() => {

$(function () {
  $("#tabs").tabs();
  $('#fileupload').fileupload({
    url: SERVER_URL + "profile/upload_avatar",
    autoUpload: true,
    acceptFileTypes: /(\.|\/)(jpe?g|png)$/i,
    start: function start() {
      $("#photo-block #loading").show();
    },
    complete: function complete() {
      $("#photo-block #loading").hide();
    }
  });
});

function show_image(file) {
  var files = file.files;
  var file = files[0];
  photo.file = file;
  var reader = new FileReader();

  reader.onload = function (aImg) {
    return function (e) {
      aImg.src = e.target.result;
    };
  }(photo);

  reader.readAsDataURL(file);
}

$('main #tabs table tr i.right').click(function () {
  var e = $(this);
  var text = this.parentElement.innerText;
  required = e.data('required');
  e.parent().empty().append($('<input/>', {
    name: e.data('name'),
    type: 'text',
    value: text,
    required: required
  }));
  $('input[name=phone]').mask('+38 (000) 000-00-00');
  $("main #tabs #main button.hide").removeClass('hide');
});

function facebookSignUp() {
  FB.login(function (response) {
    if (response.authResponse) {
      $("#divLoading").addClass('show');
      var accessToken = response.authResponse.accessToken;
      FB.api('/me?fields=email', function (response) {
        if (response.email && accessToken) {
          $('#authAlert').addClass('collapse');
          $.ajax({
            url: SITE_URL + 'profile/facebook',
            type: 'POST',
            data: {
              accessToken: accessToken,
              ajax: true
            },
            complete: function complete() {
              $("div#divLoading").removeClass('show');
            },
            success: function success(res) {
              if (res['result'] == true) {
                location.reload();
              } else {
                $('#authAlert').removeClass('collapse');
                $("#authAlertText").text(res['message']);
              }
            }
          });
        } else {
          $("div#divLoading").removeClass('show');
          $("#clientError").text('Для авторизації потрібен e-mail');
          setTimeout(function () {
            $("#clientError").text('');
          }, 5000);
          FB.api("/me/permissions", "DELETE");
        }
      });
    } else $("div#divLoading").removeClass('show');
  }, {
    scope: 'email'
  });
  return false;
}

/***/ }),

/***/ "./dev/scss/login.scss":
/*!*****************************!*\
  !*** ./dev/scss/login.scss ***!
  \*****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./dev/scss/profile.scss":
/*!*******************************!*\
  !*** ./dev/scss/profile.scss ***!
  \*******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./dev/scss/comments.scss":
/*!********************************!*\
  !*** ./dev/scss/comments.scss ***!
  \********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./dev/scss/ws__main.scss":
/*!********************************!*\
  !*** ./dev/scss/ws__main.scss ***!
  \********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./dev/scss/style.scss":
/*!*****************************!*\
  !*** ./dev/scss/style.scss ***!
  \*****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"/js/user": 0,
/******/ 			"style/style": 0,
/******/ 			"style/ws__main": 0,
/******/ 			"style/comments": 0,
/******/ 			"style/profile": 0,
/******/ 			"style/login": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunkministerstvo_localhost"] = self["webpackChunkministerstvo_localhost"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	__webpack_require__.O(undefined, ["style/style","style/ws__main","style/comments","style/profile","style/login"], () => (__webpack_require__("./dev/js/user.js")))
/******/ 	__webpack_require__.O(undefined, ["style/style","style/ws__main","style/comments","style/profile","style/login"], () => (__webpack_require__("./dev/scss/login.scss")))
/******/ 	__webpack_require__.O(undefined, ["style/style","style/ws__main","style/comments","style/profile","style/login"], () => (__webpack_require__("./dev/scss/profile.scss")))
/******/ 	__webpack_require__.O(undefined, ["style/style","style/ws__main","style/comments","style/profile","style/login"], () => (__webpack_require__("./dev/scss/comments.scss")))
/******/ 	__webpack_require__.O(undefined, ["style/style","style/ws__main","style/comments","style/profile","style/login"], () => (__webpack_require__("./dev/scss/ws__main.scss")))
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["style/style","style/ws__main","style/comments","style/profile","style/login"], () => (__webpack_require__("./dev/scss/style.scss")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;