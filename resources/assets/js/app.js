/*!
 * (c) 2016, Raphael Marco
 */

'use strict';

import Vue from 'vue'
import axios from 'axios'
import timeago from 'timeago.js'
import qs from 'qs'

Vue.directive('focus', {
    inserted: (el) => {
        el.focus()
    }
})

const isNumeric = function isNumeric(value) {
    return Number(parseFloat(value)) == value;
}

const isAllFilled = function isAllFilled(obj, excludes) {
    excludes = excludes || []

    for (let key in obj) {
        if (excludes.indexOf(key) > -1) {
            continue
        }

        if (!obj[key]) {
            return false
        }
    }

    return true
}

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
                this.show = false
            }
        }
    },

    ready: function() {
        document.addEventListener('keydown', this.escapeClose)
    }
})

let app = new Vue({
    el: '#app',
    
    data: {
        loadingOverlayActive: false,

        sideBarActive: false,
        notifBarActive: false,

        disableAction: false,
        paginationPage: 1,
        modelId: 0,

        notifications: [],

        unreadNotificationCount: 0,
        groupDeleteAction: 'move',

        showSignatureLines: false,

        request: {
            disabled: false,
            incurredBalance: '0 days',

            data: {
                start_date: null,
                start_time: null,
                end_date: null,
                end_time: null,
                subtype: null
            }
        },
        
        modals: {
            confirm: false,
            confirm2: false
        },

        buttons: {
            approveLoading: false,
            disapproveLoading: false,
            postLoading: false,
            deleteLoading: false
        }
    },
    
    methods: {
        toggleSideBar: function() {
            this.sideBarActive = !this.sideBarActive;

            this.notifBarActive = false;
        },

        toggleNotifBar: function() {
            this.notifBarActive = !this.notifBarActive
            
            this.sideBarActive = false
            
            this.unreadNotificationCount = 0

            if (this.notifBarActive) {
                var localUnreadCount = 0

                for (var i = 0; i < this.notifications.length; i++) {
                    if (this.notifications[i].read_at == null) {
                        this.notifications[i].read_at = new Date()
                        
                        localUnreadCount++
                    }

                }

                if (localUnreadCount > 0) {
                    axios.post('/account/notifications/read', {
                        ids: this.notifications.map(item => item['id'])
                    })
                }
            }
        },

        closeAllBar: function() {
            this.topBarActive = false
            this.sideBarActive = false
            this.notifBarActive = false
        },

        setFormId: function(id) {
            this.modelId = id
        },

        changePaginationPage: function() {
            var params = qs.parse(location.search.substr(1))
            params.page = this.paginationPage

            location = location.pathname + '?' + qs.stringify(params)
        },

        submitForm: function(id) {
            document.getElementById(id).submit()
        },

        getNotifications: function () {
            axios.get('/account/notifications').then(response => {
                if (!this.notifBarActive) {
                    this.unreadNotificationCount = response.data.unread_count;
                }

                for (var i = response.data.entries.length - 1; i >= 0; i--) {
                    var existingEntries = this.notifications.filter(function (item) {
                        return item.id == response.data.entries[i].id
                    })

                    if (existingEntries.length > 0) {
                        continue
                    }

                    this.notifications.unshift(response.data.entries[i])
                }
            })
        },

        dismissNotification: function (index) {
            this.notifications.splice(index, 1)
        },

        formatTime: function(time) {
            return timeago().format(time)
        },

        printDocument: function() {
            window.print()
        },

        location: function(url) {
            window.location = url
        }
    },

    watch: {
        'request.data': {
            handler: function(valueAfter, valueBefore) {
                if (!isAllFilled(valueAfter, ['subtype'])) {
                    return
                }

                let computeUrl = `${location.pathname}/compute`

                this.request.incurredBalance = 'Loading...'

                axios.post(computeUrl, valueAfter).then(response => {
                    this.request.disabled = false

                    if (response.data.incurred_balance == 1) {
                        this.request.incurredBalance = `${response.data.incurred_balance} day`

                        return
                    }

                    this.request.incurredBalance = `${response.data.incurred_balance} days`
                })
            },

            deep: true
        }
    }
})

app.paginationPage = (() => {
    let params = qs.parse(location.search.substr(1))

    if ('page' in params && isNumeric(params.page)) {
        return params.page
    }

    return 1
})()

if (isLoggedIn) {
    app.getNotifications()
    setInterval(app.getNotifications, 30000)
}

window.addEventListener('beforeunload', () => {
    if (navigator.standalone ||
        window.matchMedia('(display-mode: standalone)').matches) {
        
        app.loadingOverlayActive = true
    }
}, false)
