/*!
 * (c) 2016, Raphael Marco
 */

(function () {
    'use strict';

    var getUrlParams = function getQueryVars() {
        var params = {};
        var i = location.href.indexOf('?');

        if (i == -1) {
            return params;
        }

        var queryString = location.href.slice(i + 1);

        if (queryString.length == 0) {
            return params;
        }

        queryString = queryString.split('&');

        for (var i = 0; i < queryString.length; i++) {
            var param = queryString[i].split('=');

            params[param[0]] = param[1];
        };

        return params;
    };

    var getQueryVar = function getQueryVar(key) {
        var query = window.location.search.substring(1);
        var vars = query.split("&");
       
        for (var i=0; i < vars.length ; i++) {
            var pair = vars[i].split("=");

            if (pair[0] == key) {
                return pair[1];
            }
       }
       
       return null;
    };

    var stringifyUrlParams = function stringifyUrlParams(params) {
        var queryString = '';

        for (var i in params) {
            queryString += i + '=' + params[i] + '&';
        }

        return queryString.substring(0, queryString.length - 1);
    };

    var appData = {
        isNavActive: false,
        disableAction: false,
        paginationPage: 1,
        
        modals: {
            alert: true,
            confirm: false,
            helpBox: false,
            loading: false
        }
    };

    var appMethods = {
        toggleNav: function() {
            this.isNavActive = !this.isNavActive;
        },

        changePaginationPage: function() {
            var params = getUrlParams();
            params.page = this.paginationPage;

            location = location.pathname + '?' + stringifyUrlParams(params);
        },

        printDocument: function() {
            window.print();
        }
    };

    Vue.component('modal', {
        template: '#modal-template',
        
        props: {
            show: {
                type: Boolean,
                required: true,
                twoWay: true    
            }
        },

        methods: {
            escapeClose: function(e) {
                if (this.show && e.keyCode == 27) {
                    this.show = false;
                }
            }
        },

        ready: function() {
            document.addEventListener('keydown', this.escapeClose);
        }
    });

    var app = new Vue({
        el: '#app',
        data: appData,
        methods: appMethods
    });
}());