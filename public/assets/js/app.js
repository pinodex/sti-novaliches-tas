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

    var isNumeric = function isNumeric(value) {
        return Number(parseFloat(value)) == value;
    }

    var appData = {
        sidebarActive: false,
        disableAction: false,
        userExtraInfo: false,
        paginationPage: 1,
        modelId: 0,

        groupDeleteAction: 'move',

        request: {
            start_date: null,
            start_time: null,
            end_date: null,
            end_time: null
        },

        inbox: {
            action: null,
            disapproval_reason: null
        },
        
        modals: {
            alert: true,
            confirm: false,
            confirm2: false,
            helpBox: false,
            loading: false
        }
    };

    var appComputed = {
        requestBalance: function () {
            if (!this.request.start_date ||
                !this.request.start_time ||
                !this.request.end_date   ||
                !this.request.end_time    ) {

                return 0;
            }

            var startDate = new Date(this.request.start_date + ' ' + this.request.start_time);
            var endDate = new Date(this.request.end_date + ' ' + this.request.end_time);

            var days = endDate.getDate() - startDate.getDate();
            var startHour = startDate.getHours() + (startDate.getMinutes() / 6 / 10);
            var endHour = endDate.getHours() + (endDate.getMinutes() / 6 / 10);

            if (days < 0) {
                return 0;
            }

            if (endHour - startHour < 4) {
                days += 0.5;
            }

            if (endHour - startHour >= 4) {
                days += 1;
            }

            return days;
        }
    };

    var appMethods = {
        toggleNav: function() {
            this.isNavActive = !this.isNavActive;
        },

        setFormId: function(id) {
            this.modelId = id;
        },

        changePaginationPage: function() {
            var params = getUrlParams();
            params.page = this.paginationPage;

            location = location.pathname + '?' + stringifyUrlParams(params);
        },

        submitForm: function(id) {
            document.getElementById(id).submit();
        },

        printDocument: function() {
            window.print();
        },

        location: function(url) {
            window.location = url;
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
        methods: appMethods,
        computed: appComputed
    });

    var currentPage = getQueryVar('page');

    if (isNumeric(currentPage)) {
        app.$set(appData, 'paginationPage', currentPage);
    }
}());
