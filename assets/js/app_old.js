import '@popperjs/core';
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';

import './../../bootstrap.js';

import './../css/app-saas.css';
import './../css/icons.css';
import './../css/app.css';


import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.bootstrap5.css';

// import './fileupload.js';

import 'simplebar';

import 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import './../css/flatpickr_dark.css';

import './invoice.js';

import './api_form.js';

import './tinymce.js';

$.widget('custom.languagetoggle', {

    state: null,

    options: {
        state: null,
        cookie: null,
        cookieOptions: {},
        event: "click",
        target: null,
        targetClassOn: "toggle-on",
        targetClassOff: "toggle-off",
        handleClassOn: "toggle-on",
        handleClassOff: "toggle-off"
    },

    _create: function () {
        var self = this;
        var state = true;

        if (this.options.state !== null) {
            state = this.options.state;
        }

        if (this.options.cookie !== null) {
            var cookie = $.cookie(this.options.cookie);
            if (!!cookie) {
                if (cookie == '1') {
                    state = true;
                } else {
                    state = false;
                }
            }
        }

        this.setState(state);

        if (state) {
            $('#' + this.element.attr('for'))[0].checked = true;
        } else {
            $('#' + this.element.attr('for'))[0].checked = false;
        }


        this.element.on(this.options.event, function () {
            self.toggleState();
        });
    },

    _setState: function (state) {
        var target = $(this.options.target).not(this.element);
        if (state) {
            this.element.removeClass(this.options.handleClassOff).addClass(this.options.handleClassOn);
            target.removeClass(this.options.targetClassOff).addClass(this.options.targetClassOn);
        } else {
            this.element.removeClass(this.options.handleClassOn).addClass(this.options.handleClassOff);
            target.removeClass(this.options.targetClassOn).addClass(this.options.targetClassOff);
        }
    },

    setState: function (state) {
        this.state = state;
        this._setState(state);
        if (this.options.cookie !== null) {
            $.cookie(this.options.cookie, this.state ? '1' : '0', this.options.cookieOptions);
        }
    },

    toggleState: function () {
        this.setState(!this.state);
    }

});

var createToggles = function () {
    $('[data-cookie-toggle]').each(function () {
        var data = $(this).attr("data-cookie-toggle");
        data = (typeof (data) != 'undefined' && data != "") ? JSON.parse(data) : {};
        $(this).languagetoggle(data);
    });
    $(window).trigger('createToggles', {});
};

var createTomSelect = function () {
    document.querySelectorAll('.ds-tom-select, select.select2').forEach((el) => {
        if(el.tomselect === undefined && (el.tagName.toUpperCase() === 'SELECT' || el.tagName.toUpperCase() === 'INPUT')) {
            let settings = {
                // create: true,
            };

            if (el.hasAttribute('data-ajax')) {
                settings['load'] = function (query, callback) {
                    var url = el.getAttribute('data-ajax') + '?q=' + encodeURIComponent(query);
                    fetch(url)
                        .then(response => response.json())
                        .then(json => {
                            callback(json.items);
                        }).catch(() => {
                        callback();
                    });
                }

                if (el.hasAttribute('max-items')) {
                    settings['maxItems'] = el.getAttribute('max-items');
                } else {
                    settings['maxItems'] = 1;
                }

                if (el.hasAttribute('max-options')) {
                    settings['maxOptions'] = el.getAttribute('max-options');
                } else {
                    settings['maxOptions'] = null;
                }
            }
            new TomSelect(el, settings);
        } else {
            // el.tomselect.clear(true);
            // el.tomselect.clearOptionGroups();
            // el.tomselect.clearOptions();
            // el.tomselect.sync();
            // el.tomselect.refreshItems();
            // el.tomselect.refreshOptions(false);
        }
    });
};

// $(document).on('select2:open', () => {
//     setTimeout(() => document.querySelector('.select2-container--open .select2-search__field').focus(), 200);
// });

// $('[data-toggle="popover"]').popover();
var $datepicker = function () {
    $("input[data-datepicker]").each(function (key, element) {
        var datepickerDefaults = {
            altInput: true,
            altFormat: 'd.m.Y',
            dateFormat: 'Y-m-d',
            locale: {
                firstDayOfWeek: 1 // start week on Monday
            },
            enableTime: false,
            allowInput: true,
        };

        var data = $(this).attr("data-datepicker");
        data = (typeof (data) != 'undefined' && data != "") ? JSON.parse(data) : {};

        var options = {};
        $.extend(options, datepickerDefaults, data);

        if (data.enableTime == true) {
            datepickerDefaults.altFormat = 'd.m.Y H:i';
            datepickerDefaults.dateFormat = 'Y-m-d H:i';
            datepickerDefaults.time_24hr = true;
        }
        $.extend(options, datepickerDefaults, data);
        element.flatpickr(options);
    });
};

$(document).ready(function () {

    // createToggles();

    createTomSelect();

    $datepicker();

    $(document).on('modal-form-load', function () {
        $datepicker();
        createTomSelect();
    });

    $(window).bind('beforeunload', function () {
        $('#loader').addClass('show');
    });

    $(window).bind('unload', function () {
        $('#loader').removeClass('show');
    });

    $(window).bind('unload', function () {
        $('#loader').removeClass('show');
    });

    $('.top-language-dropdown').on('click', function (e) {
        e.stopPropagation();
    });
    //
    // $('#invoice_search_form_client').select2({
    //     dropdownParent: $('#searchForm')
    // });
    //
    // $('#invoice_search_form_isPaid').select2({
    //     dropdownParent: $('#searchForm')
    // });
    //
    // $('#invoice_search_form_invoicePaymentMethod').select2({
    //     dropdownParent: $('#searchForm')
    // });
    //
    // $('#expense_invoice_search_form_client').select2({
    //     dropdownParent: $('#searchForm')
    // });
    //
    // $('#expense_invoice_search_form_invoicePaymentMethod').select2({
    //     dropdownParent: $('#searchForm')
    // });
    //
    // $('#expense_invoice_search_form_isPaid').select2({
    //     dropdownParent: $('#searchForm')
    // });
    //
    // $('#proform_invoice_search_form_client').select2({
    //     dropdownParent: $('#searchForm')
    // });
    //
    // $('#proform_invoice_search_form_isPaid').select2({
    //     dropdownParent: $('#searchForm')
    // });
    //
    // $('#proform_invoice_search_form_invoicePaymentMethod').select2({
    //     dropdownParent: $('#searchForm')
    // });

    if (document.cookie.indexOf('new_feature_block=1') === -1) {
        $('#newFeatureCloseBlock').addClass('active');

        function setCookie(name, value) {
            var date = new Date();
            date.setTime(date.getTime() + (60 * 24 * 60 * 60 * 1000));
            document.cookie = name + '=' + value + "; expires=" + date.toUTCString();
        }

        $('#newFeatureCloseBtn').on('click', function () {
            setCookie('new_feature_block', 1);
            $('#newFeatureCloseBlock').removeClass('active');
        });
    }

    function getOffsetTop(elem) {
        var offsetLeft = 0;
        do {
            if (!isNaN(elem.offsetTop)) {
                offsetLeft += elem.offsetTop;
            }
        } while (elem = elem.offsetParent);
        return offsetLeft;
    }

    function getOffsetLeft(elem) {
        var offsetLeft = 0;
        do {
            if (!isNaN(elem.offsetLeft)) {
                offsetLeft += elem.offsetLeft;
            }
        } while (elem = elem.offsetParent);
        return offsetLeft;
    }

    function setStickyStyle(el, top, left) {
        el.style.position = 'fixed';
        el.style.top = top + 'px';
        el.style.left = parseInt(left) + 'px';
        // el.style.width = '100%';
        // el.style.display = 'none';
    }

    // STICKY TABLE HEADS
    let tables = document.querySelectorAll('table[data-table-sticky]');
    if (tables.length > 0) {

        let divContainersArr = [];
        let tHeadsArr = [];
        let topNavBar = document.querySelector('.navbar-custom');
        let topNavBarHeight = topNavBar.getBoundingClientRect().height

        for (let tableIndex = 0; tableIndex < tables.length; tableIndex++) {
            let table = tables[tableIndex];
            let tHead = table.querySelector('thead');
            let tHeadClone = tHead.cloneNode(true);

            // setStickyStyle(tHeadClone, topNavBarHeight, tHead.getBoundingClientRect().left);
            let tHeadThs = tHead.querySelectorAll('th');
            // tHeadClone.style.width = tHead.getBoundingClientRect().width + 'px';
            for (let i = 0; i < tHeadThs.length; i++) {
                tHeadClone.querySelector('th:nth-child(' + (i + 1) + ')').style.width = tHeadThs[i].getBoundingClientRect().width + 'px';
            }

            const divContainer = document.createElement('div');
            divContainer.classList.add('table-responsive');
            divContainer.style.overflow = 'hidden';
            divContainer.style.visibility = 'hidden';
            divContainer.style.zIndex = '1';
            divContainer.style.width = table.parentNode.getBoundingClientRect().width + 'px';
            setStickyStyle(divContainer, topNavBarHeight, tHead.getBoundingClientRect().left);

            divContainersArr.push(divContainer);
            tHeadsArr.push(tHead);

            const tableClone = document.createElement('table');
            tableClone.classList.add('table', 'table-centered', 'dt-responsive', 'nowrap', 'table-bordered', 'ds-table-sm');
            tableClone.style.width = table.getBoundingClientRect().width + 'px';
            tableClone.appendChild(tHeadClone);
            divContainer.appendChild(tableClone);
            table.parentNode.parentNode.insertBefore(divContainer, table.parentNode);

        }

        for (let tableIndex = 0; tableIndex < tables.length; tableIndex++) {
            tables[tableIndex].parentNode.addEventListener('scroll', function (e) {
                divContainersArr[tableIndex].scrollTo(e.target.scrollLeft, 0);
            });
        }

        window.onscroll = function () {
            setStickyTableHead()
        };

        // window.onresize = function () {setStickyTableHead()};

        function setStickyTableHead() {
            for (let tableIndex = 0; tableIndex < tables.length; tableIndex++) {
                if (tHeadsArr[tableIndex].getBoundingClientRect().top - topNavBarHeight < 0 && tables[tableIndex].getBoundingClientRect().bottom - topNavBarHeight - 50 > 0) {
                    divContainersArr[tableIndex].style.visibility = 'visible';
                } else {
                    divContainersArr[tableIndex].style.visibility = 'hidden';
                }
            }
        }

        setStickyTableHead();
    }

    var invoiceNetField = $('#expense_invoice_form_net');
    var invoiceVatRate = $('#expense_invoice_form_vatRate');
    var invoiceVat = $('#expense_invoice_form_vat');
    var invoiceTotal = $('#expense_invoice_form_total');


    var fields = $('#expense_invoice_form_net, #expense_invoice_form_vatRate');

    if (fields.length > 0) {
        $('#expense_invoice_form_net, #expense_invoice_form_vatRate').on('keyup', setTotalAndVat);
        setTotalAndVat();
    }

    function setTotalAndVat() {
        var net = invoiceNetField.val().replace(',', '.');
        var vat = 0;
        if (net > 0) {
            vat = invoiceVatRate.val();
            var vat = (net * invoiceVatRate.val() / 100).toFixed(2);
            var total = parseFloat(net) + parseFloat(vat);
            var totalFloat = total.toFixed(2);
            if (vat > 0) {
                invoiceVat.val(vat);
            } else {
                invoiceVat.val(null);
            }

            invoiceTotal.val(totalFloat);
        } else {
            invoiceVat.val(null);
            invoiceTotal.val(null);
        }
    }

    $(document).on('click', '.reject-button', function () {
        $('.obj-form').show();
    });

    $(document).on('click', '.obj-form__cancel', function () {
        $('.obj-form').hide();
    })

    $(document).on('click', '#companyClientFormGenerate', function () {
        $('#loader').addClass('show');
        var eik = $('#company_client_form_eik').val();
        console.log(eik);
        if(eik) {
            var request = $.ajax({
                url: $(this).attr('data-url') + '?eik=' + eik,
                method: "GET",
                dataType: "json",
                headers: {"X-Requested-With": "DSModalRequest"}
            });
            request.done(function (msg) {
                $('#loader').removeClass('show');
                if(msg) {
                    $('#company_client_form_ddsNumber').val(msg.vat);
                    $('#company_client_form_name').val(msg.name);
                    $('#company_client_form_address').val(msg.address);
                }
            });
        }
    });
});

!function ($) {
    "use strict";

    function t() {
        this.body = document.querySelector('body'), this.window = document.window, this.menuContainer = document.getElementById('left-side-menu-container');
    }

    document.querySelector('.be-nav li > span').on('click', function () {
        var parent = this.parentNode;
        parent.classList.toggle('current_ancestor')
    });

    t.prototype.initMenu = function () {
        var $this = this;
        $(document).on("click", ".button-menu-mobile", function (t) {
            t.preventDefault(),
                $this.body.toggleClass("sidebar-enable")
        });
    };
    t.prototype.init = function () {
        this.initMenu()
    };

    $.LeftSidebar = new t;
    $.LeftSidebar.Constructor = t;
    $.LeftSidebar.init();

    $(".customCheckAll").on('click', function () {
        $(".customCheckAll").closest('table').find('input[type=checkbox][name^=\'checked\']').prop('checked', this.checked).trigger('change');
    });

    $("[data-password]").on("click", function () {
        "false" == $(this).attr("data-password") ? ($(this).siblings("input").attr("type", "text"), $(this).attr("data-password", "true"), $(this).addClass("show-password")) : ($(this).siblings("input").attr("type", "password"), $(this).attr("data-password", "false"), $(this).removeClass("show-password"))
    });

    $("#GenerateRandomPassword").on('click', function () {
        var chars = "0123456789abcdefghijklmnopqrstuvwxyz!@#$%^&*()ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        var passwordLength = 12;
        var password = "";
        for (var i = 0; i <= passwordLength; i++) {
            var randomNumber = Math.floor(Math.random() * chars.length);
            password += chars.substring(randomNumber, randomNumber + 1);
        }
        $(this).siblings("input").val(password);
        if ($("[data-password]").attr("data-password") == "false") {
            $("[data-password]").click();
        }
    });

    function getBackUrlsFromSessionStore() {
        let backUrls = sessionStorage.getItem('backUrl');
        if (backUrls === null) {
            backUrls = [];
        } else {
            backUrls = JSON.parse(backUrls);
        }
        return backUrls;
    }

    var dsCmsBackBtn = $('.ds-cms-back-btn');
    if (dsCmsBackBtn.length > 0) {
        // console.log(document.referrer);
        if (document.referrer) {
            if (document.referrer !== document.URL) {
                let backUrls = getBackUrlsFromSessionStore();
                let clickBackBtn = sessionStorage.getItem('clickBackBtn');
                if (clickBackBtn === null) {
                    if (backUrls.length > 0 && backUrls[backUrls.length - 1] === document.referrer) {
                        // not push in backUrls array
                    } else {
                        backUrls.push(document.referrer);
                        sessionStorage.setItem('backUrl', JSON.stringify(backUrls));
                    }
                } else {
                    sessionStorage.removeItem('clickBackBtn');
                }
            }
        }
        dsCmsBackBtn.click(function (e) {
            e.preventDefault();
            let backUrls = getBackUrlsFromSessionStore();
            let url = '';
            if (e.currentTarget.hasAttribute('data-return-to-module-list')) {
                let backUrlsCopy = [...backUrls];
                backUrlsCopy.reverse();
                for (let i = 0; i < backUrlsCopy.length; i++) {
                    let urlParts = new URL(backUrlsCopy[i]);
                    if (urlParts['pathname'] == e.currentTarget.getAttribute('href')) {
                        url = backUrlsCopy[i];
                        break;
                    }
                }
                if (url === '') {
                    let newBackUrls = [];
                    for (let i = 0; i < backUrls.length; i++) {
                        if (url = backUrls[i]) {
                            break;
                        }
                        newBackUrls.push(backUrls[i]);
                    }
                    backUrls = newBackUrls;
                }
            }

            if (url === '') {
                url = backUrls.pop();
            }

            if (url === '') {
                url = e.currentTarget.getAttribute('href');
            }

            if (url !== undefined) {
                window.location.href = url;
                sessionStorage.setItem('backUrl', JSON.stringify(backUrls));
                sessionStorage.setItem('clickBackBtn', '1');
            } else {
                window.location.href = e.target.href;
            }
        });
        let backUrls = getBackUrlsFromSessionStore();
        if (backUrls.length > 0) {
            $('form[data-form-save]').each(function (index, element) {
                let input = document.createElement("input");
                input.setAttribute('type', 'hidden');
                input.setAttribute('name', 'backUrlByClickBtn');
                input.setAttribute('value', JSON.stringify(backUrls));
                element.append(input);
            });
        }
    } else {
        sessionStorage.setItem('backUrl', JSON.stringify([]));
        sessionStorage.removeItem('clickBackBtn');
    }

}($);

// !function () {
//     'use strict';
//
//     const applicationServerPublicKey = 'BORav_yLyIqxGL1F3z08fYOs01B50X0s1_sYL8Ml_5HYVwhn-XIrSpPPR6rojjL-2PIOoQaGecVD776z3hdYI5w\n';
//
//     const pushButton = document.querySelector('.js-push-btn');
//
//     let isSubscribed = false;
//     let swRegistration = null;
//
//     function urlB64ToUint8Array(base64String) {
//         const padding = '='.repeat((4 - base64String.length % 4) % 4);
//         const base64 = (base64String + padding)
//             .replace(/\-/g, '+')
//             .replace(/_/g, '/');
//
//         const rawData = window.atob(base64);
//         const outputArray = new Uint8Array(rawData.length);
//
//         for (let i = 0; i < rawData.length; ++i) {
//             outputArray[i] = rawData.charCodeAt(i);
//         }
//         return outputArray;
//     }
//
//     if ('serviceWorker' in navigator && 'PushManager' in window) {
//         console.log('Service Worker and Push is supported');
//
//         navigator.serviceWorker.register('/service-worker.js')
//             .then(function (swReg) {
//                 // console.log('Service Worker is registered', swReg);
//
//                 swRegistration = swReg;
//                 initializeUI();
//             })
//             .catch(function (error) {
//                 console.error('Service Worker Error', error);
//             });
//     } else {
//         console.warn('Push messaging is not supported');
//         if(pushButton) {
//             pushButton.textContent = 'Push Not Supported';
//         }
//     }
//
//     function initializeUI() {
//         if(pushButton) {
//             pushButton.addEventListener('click', function () {
//                 pushButton.disabled = true;
//                 if (isSubscribed) {
//                     unsubscribeUser();
//                 } else {
//                     subscribeUser();
//                 }
//             });
//         }
//
//         // Set the initial subscription value
//         swRegistration.pushManager.getSubscription()
//             .then(function (subscription) {
//                 isSubscribed = !(subscription === null);
//
//                 let adminPushNotificationHash = getCookie('push_notification_hash')
//                 if (adminPushNotificationHash == "" || adminPushNotificationHash == -1) {
//                     updateSubscriptionOnServer(subscription, 'POST');
//                 }
//
//                 if (isSubscribed) {
//                     console.log('User IS subscribed.');
//                 } else {
//                     console.log('User is NOT subscribed.');
//                     subscribeUser();
//                 }
//
//                 updateBtn();
//             });
//     }
//
//     function updateBtn() {
//         if(pushButton) {
//             if (Notification.permission === 'denied') {
//                 pushButton.textContent = 'Push Messaging Blocked.';
//                 pushButton.disabled = true;
//                 updateSubscriptionOnServer(null);
//                 return;
//             }
//
//             if (isSubscribed) {
//                 pushButton.textContent = 'Disable Push Messaging';
//             } else {
//                 pushButton.textContent = 'Enable Push Messaging';
//             }
//
//             pushButton.disabled = false;
//         }
//     }
//
//     function subscribeUser() {
//         const applicationServerKey = urlB64ToUint8Array(applicationServerPublicKey);
//         swRegistration.pushManager.subscribe({
//             userVisibleOnly: true,
//             applicationServerKey: applicationServerKey
//         })
//             .then(function (subscription) {
//                 console.log('User is subscribed.');
//
//                 updateSubscriptionOnServer(subscription, 'POST');
//
//                 isSubscribed = true;
//
//                 updateBtn();
//             })
//             .catch(function (err) {
//                 console.log('Failed to subscribe the user: ', err);
//                 updateBtn();
//             });
//     }
//
//     function setCookie(cname, cvalue, exdays) {
//         var d = new Date();
//         d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
//         var expires = "expires=" + d.toUTCString();
//         document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
//     }
//
//     function getCookie(cname) {
//         var name = cname + "=";
//         var decodedCookie = decodeURIComponent(document.cookie);
//         var ca = decodedCookie.split(';');
//         for (var i = 0; i < ca.length; i++) {
//             var c = ca[i];
//             while (c.charAt(0) == ' ') {
//                 c = c.substring(1);
//             }
//             if (c.indexOf(name) == 0) {
//                 return c.substring(name.length, c.length);
//             }
//         }
//         return "";
//     }
//
//     function updateSubscriptionOnServer(subscription, method) {
//
//         if (subscription === null) {
//             return;
//         }
//         const key = subscription.getKey('p256dh');
//         const token = subscription.getKey('auth');
//         const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];
//
//         return fetch('/user-push-notification/', {
//             method,
//             body: JSON.stringify({
//                 endpoint: subscription.endpoint,
//                 publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
//                 authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
//                 contentEncoding,
//             }),
//         })
//             .then(response => response.json())
//             .then(data => {
//                 if (data.hash !== undefined) {
//                     setCookie('push_notification_hash', data.hash, 1);
//                 }
//             })
//             .catch((error) => {
//                 console.error('Error:', error);
//             });
//     }
//
//     function unsubscribeUser() {
//         swRegistration.pushManager.getSubscription()
//             .then(function (subscription) {
//                 if (subscription) {
//
//                     updateSubscriptionOnServer(subscription, 'DELETE');
//
//                     console.log('User is unsubscribed.');
//                     isSubscribed = false;
//
//                     subscription.unsubscribe()
//
//                     updateBtn();
//
//                     return true;
//                 }
//             })
//             .catch(function (error) {
//                 console.log('Error unsubscribing', error);
//             });
//     }
//
//     $('.document-view-iframe-btn').on('click', function(){
//         $('.document-view-iframe').attr('src', $(this).attr('data-document-view-iframe-link'));
//     });
//     $('.document-view-iframe-modal').on('hidden.bs.modal', function (e) {
//         $('.document-view-iframe').attr('src', '');
//     });
// }();

console.log(
    '%cWelcome to DS Module System 👋' + '\n' +
    '%c\n' +
    '\n' +
    '📖 For more information: %chttps://www.dsmodulesystem.com/%c' +
    '\n',
    'font-family: monospace; font-size: 24px; font-weight: bold;',
    'font-family: monospace; font-size: 14px;',
    'font-family: monospace; font-size: 14px;',
    'font-family: monospace; font-size: 14px; text-decoration: none;',
);
