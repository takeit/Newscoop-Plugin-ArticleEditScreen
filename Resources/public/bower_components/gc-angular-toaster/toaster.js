'use strict';

/*
 * Good Coders - AngularJS Toaster
 * Version: 1.0.0
 *
 * Copyright 2013 Jiri Kavulak.  
 * All Rights Reserved.  
 * Use, reproduction, distribution, and modification of this code is subject to the terms and 
 * conditions of the MIT license, available at http://www.opensource.org/licenses/mit-license.php
 *
 * Author: Jiri Kavulak
 * Related to project of John Papa and Hans Fjällemark
 */
angular.module('gc.toaster.tpls',[]).run(['$templateCache', function($templateCache){
	$templateCache.put('template/toaster/toaster.html',
		'<div  id="toast-container" ng-class="config.position">' +
			'<div ng-repeat="toaster in toasters" class="toast" ng-class="toaster.type" ng-click="click(toaster)" ng-mouseover="stopTimer(toaster)"  ng-mouseout="restartTimer(toaster)">' +
			'<div ng-class="config.title">{{toaster.title}}</div>' +
			'<div ng-class="config.message" ng-switch on="toaster.messageOutputType">' +
			'<div ng-switch-when="trustedHtml" ng-bind-html="toaster.html"></div>' +
			'<div ng-switch-when="template"><div ng-include="toaster.messageTemplate"></div></div>' +
			'<div ng-switch-default >{{toaster.message}}</div>' +
			'</div>' +
			'</div>' +
			'</div>');
}]);

angular.module('gc.toaster', ['gc.toaster.tpls','ngAnimate'])
.service('toaster', ['$rootScope', function ($rootScope) {
    this.add = function (settings) {
        this.toast = angular.extend({
            type: null,
            title: null,
            message: null,
            timeout: null,
            messageOutputType: null,
            clickHandler: null
        },settings);
        $rootScope.$broadcast('toaster-newToast');
    };

    this.clear = function () {
        $rootScope.$broadcast('toaster-clearToasts');
    };
}])
.constant('toasterConfig', {
    'limit': 0,                   // limits max number of toasts 
    'tap-to-dismiss': true,
    'newest-on-top': true,
    //'fade-in': 1000,            // done in css
    //'on-fade-in': undefined,    // not implemented
    //'fade-out': 1000,           // done in css
    // 'on-fade-out': undefined,  // not implemented
    //'extended-time-out': 1000,    // not implemented
    'time-out': 5000, // Set timeOut and extendedTimeout to 0 to make it sticky
    'message-output-type': '', // Options: '', 'trustedHtml', 'template'
    'message-template': 'toasterBodyTmpl.html',
    'position-class': 'toast-top-right',
    'title-class': 'toast-title',
    'message-class': 'toast-message'
})
.directive('toasterContainer', ['$compile', '$timeout', '$sce', 'toasterConfig', 'toaster',
function ($compile, $timeout, $sce, toasterConfig, toaster) {
    return {
        replace: true,
        restrict: 'EA',
        scope: true, // creates an internal scope for this directive
        link: function (scope, elm, attrs) {

            var id = 0,
                mergedConfig;

            mergedConfig = angular.extend({}, toasterConfig, scope.$eval(attrs.toasterOptions));

            scope.config = {
                position: mergedConfig['position-class'],
                title: mergedConfig['title-class'],
                message: mergedConfig['message-class'],
                tap: mergedConfig['tap-to-dismiss']
            };

            scope.configureTimer = function configureTimer(toast) {
                var timeout = typeof (toast.timeout) == "number" ? toast.timeout : mergedConfig['time-out'];
                if (timeout > 0)
                    setTimeout(toast, timeout);
            };

            function addToast(toast) {
				toast.type = 'toast-' + toast.type;
                id++;
                angular.extend(toast, { id: id });

                // Set the toast.messageOutputType to the default if it isn't set
                toast.messageOutputType = toast.messageOutputType || mergedConfig['message-output-type'];
                switch (toast.messageOutputType) {
                    case 'trustedHtml':
                        toast.html = $sce.trustAsHtml(toast.message);
                        break;
                    case 'template':
                        toast.messageTemplate = toast.message || mergedConfig['message-template'];
                        break;
                }

                scope.configureTimer(toast);

                if (mergedConfig['newest-on-top'] === true) {
                    scope.toasters.unshift(toast);
                    if (mergedConfig['limit'] > 0 && scope.toasters.length > mergedConfig['limit']) {
                        scope.toasters.pop();
                    }
                } else {
                    scope.toasters.push(toast);
                    if (mergedConfig['limit'] > 0 && scope.toasters.length > mergedConfig['limit']) {
                        scope.toasters.shift();
                    }
                }
            }

            function setTimeout(toast, time) {
                toast.timeout = $timeout(function () {
                    scope.removeToast(toast.id);
                }, time);
            }

            scope.toasters = [];
            scope.$on('toaster-newToast', function () {
                addToast(toaster.toast);
            });

            scope.$on('toaster-clearToasts', function () {
                scope.toasters.splice(0, scope.toasters.length);
            });
        },
        controller: ['$scope', '$element', '$attrs', function ($scope, $element, $attrs) {

            $scope.stopTimer = function (toast) {
                if (toast.timeout) {
                    $timeout.cancel(toast.timeout);
                    toast.timeout = null;
                }
            };

            $scope.restartTimer = function (toast) {
                if (!toast.timeout)
                    $scope.configureTimer(toast);
            };

            $scope.removeToast = function (id) {
                var i = 0;
                for (i; i < $scope.toasters.length; i++) {
                    if ($scope.toasters[i].id === id)
                        break;
                }
                $scope.toasters.splice(i, 1);
            };

            $scope.click = function (toaster) {
                if ($scope.config.tap === true) {
                    if (toaster.clickHandler) {
                        $scope.$parent.$eval(toaster.clickHandler)(toaster);
                    } else {
                        $scope.removeToast(toaster.id);
                    }
                }
            };
        }],
        templateUrl: 'template/toaster/toaster.html'
    };
}]);
