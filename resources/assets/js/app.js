/*!
 * (c) 2016, Raphael Marco
 */

'use strict';

import Vue from 'vue'
import axios from 'axios'
import timeago from 'timeago.js'
import qs from 'qs'

const isNumeric = function isNumeric(value) {
    return Number(parseFloat(value)) == value;
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
        sideBarActive: false,
        notifBarActive: false,

        disableAction: false,
        paginationPage: 1,
        modelId: 0,

        notifications: [],

        unreadNotificationCount: 0,
        groupDeleteAction: 'move',

        request: {
            start_date: null,
            start_time: null,
            end_date: null,
            end_time: null,

            form: {
                sick_leave: {
                    reason: null
                }
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
    
    computed: {
        requestBalance: function () {
            if (!this.request.start_date ||
                !this.request.start_time ||
                !this.request.end_date   ||
                !this.request.end_time    ) {

                return 0
            }

            var startDate = new Date(this.request.start_date + ' ' + this.request.start_time)
            var endDate = new Date(this.request.end_date + ' ' + this.request.end_time)

            var days = endDate.getDate() - startDate.getDate()
            var startHour = startDate.getHours() + (startDate.getMinutes() / 6 / 10)
            var endHour = endDate.getHours() + (endDate.getMinutes() / 6 / 10)

            if (days < 0) {
                return 0
            }

            if (endHour - startHour < 4) {
                days += 0.5
            }

            if (endHour - startHour >= 4) {
                days += 1
            }

            return days
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
