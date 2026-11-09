import '@popperjs/core';
import 'bootstrap';
// import 'bootstrap/dist/css/bootstrap.min.css';
//
import './bootstrap.js';
//
import './css/app-saas.css';
import './css/icons.css';
import './css/app.css';

import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.bootstrap5.css';

import './js/fileupload.js';

import './libs/jquery-ui-1.13.2/external/jquery/jquery.js'
import './libs/jquery-ui-1.13.2/jquery-ui.min.js'

import 'simplebar';

import 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import './css/flatpickr_dark.css';

import './js/api_form.js';

import './js/tinymce.js';

// Import and use the module loader utility
import { loadAllModules } from './js/module-loader.js';

// Load modules when DOM is ready
document.addEventListener('DOMContentLoaded', async () => {
    await loadAllModules();
});

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
                settings['valueField'] = 'id';
                settings['labelField'] = 'title';
                settings['loadThrottle'] = 0;
                settings['load'] = function (query, callback) {
                    var url = el.getAttribute('data-ajax') + '?q=' + encodeURIComponent(query);
// console.log(el.id);
                    if(el.id === 'product_form_markItemSeries') {
                        url += '&markId=' + $('#product_form_mark').val();
                    }
                    console.log(url);
                    fetch(url)
                        .then(response => response.json())
                        .then(json => {
                            console.log(json);
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
        var eek = $('#company_client_form_eek').val();
        console.log(eek);
        if(eek) {
            var request = $.ajax({
                url: $(this).attr('data-url') + '?eek=' + eek,
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
        this.body = $("body"), this.window = $(window), this.menuContainer = $("#left-side-menu-container");
    }

    $('.be-nav li > span').on('click', function () {
        var parent = $(this).parent();
        if (parent.hasClass('current_ancestor')) {
            parent.removeClass('current_ancestor');
        } else {
            parent.addClass('current_ancestor');
        }
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

document.addEventListener('DOMContentLoaded', function () {
    // For all forms on the page
    document.addEventListener('keydown', function(event) {
        // Проверка дали target е вътре във формуляр
        const isInForm = event.target.closest('form');
        
        // Ако е натиснат Enter и е в <form> (без textarea), спри submit
        if (event.key === 'Enter' && isInForm && event.target.tagName !== 'TEXTAREA') {
            event.preventDefault();
        }
    });
});

// API v1 function to load content from data-url-list attribute
function loadApiV1Content(targetElement = null) {
    let apiElements;
    

    
    if (targetElement) {
        // If targetElement is provided, ensure it's a valid DOM element
        if (targetElement && targetElement.nodeType === Node.ELEMENT_NODE) {
            apiElements = [targetElement];
        } else {
            console.error('Invalid target element provided to loadApiV1Content:', targetElement);
            console.error('targetElement type:', typeof targetElement);
            console.error('targetElement nodeType:', targetElement ? targetElement.nodeType : 'null');
            return;
        }
    } else {
        // Get all .api-v1 elements
        apiElements = document.querySelectorAll('.api-v1');
    }
    
    // Convert NodeList to Array for consistent handling
    const elementsArray = Array.from(apiElements);
    
    // Check if we have any elements to process
    if (elementsArray.length === 0) {
        console.log('No .api-v1 elements found on the page');
        return;
    }
    
    elementsArray.forEach(element => {
        // Ensure element is a valid DOM element
        if (!element || element.nodeType !== Node.ELEMENT_NODE) {
            console.error('Invalid element found in loadApiV1Content');
            return;
        }
        
        const url = element.getAttribute('data-url-list');
        
        if (url) {
            // Show loader before starting the request
            element.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Зареждане...</span>
                    </div>
                    <div class="mt-2 text-muted">Зареждане на съдържание...</div>
                </div>
            `;
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                element.innerHTML = html;
                
                // After loading content, set up event listeners for buttons
                setupApiV1Buttons(element);
            })
            .catch(error => {
                console.error('Error loading API v1 content:', error);
                element.innerHTML = `
                    <div class="alert alert-danger">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <div>
                                <strong>Грешка при зареждане:</strong><br>
                                ${error.message}
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-danger retry-button">
                                <i class="fas fa-redo me-1"></i>Опитай отново
                            </button>
                        </div>
                    </div>
                `;
                
                // Add event listener for retry button
                const retryButton = element.querySelector('.retry-button');
                if (retryButton) {
                    retryButton.addEventListener('click', function() {
                        // Ensure element is still valid before retrying
                        if (element && element.nodeType === Node.ELEMENT_NODE) {
                            loadApiV1Content(element);
                        } else {
                            console.error('Element is no longer valid for retry');
                            // Fallback to loading all API v1 content
                            loadApiV1Content();
                        }
                    });
                }
            });
        }
    });
}

// Function to set up event listeners for buttons in API v1 content
function setupApiV1Buttons(container) {
    // Find all buttons/links that should be handled by AJAX
    const buttons = container.querySelectorAll('a[href], button[data-ajax-url]');
    
    buttons.forEach(button => {
        // Skip if button already has event listener
        if (button.hasAttribute('data-ajax-handled')) {
            return;
        }
        
        const url = button.getAttribute('href') || button.getAttribute('data-ajax-url');
        
        // Check if this is a create/edit form URL (contains /create or /edit)
        if (url && (url.includes('/create') || url.includes('/edit'))) {
            button.setAttribute('data-ajax-handled', 'true');
            
            button.addEventListener('click', function(e) {
                e.preventDefault();
                // Find the parent .api-v1 container
                const parentApiContainer = button.closest('.api-v1');
                loadContentInModal(url, button.textContent.trim(), parentApiContainer);
            });
        }
    });
}

// Function to load content in modal
function loadContentInModal(url, title = 'Form', sourceElement = null) {
    // Create modal if it doesn't exist
    
    let modal = document.getElementById('api-v1-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'api-v1-modal';
        modal.className = 'modal fade';
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('aria-labelledby', 'api-v1-modal-label');
        modal.setAttribute('aria-hidden', 'true');
        
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="api-v1-modal-label">${title}</h5>
                        <button type="button" class="btn-close" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add event listener for close button
        const closeButton = modal.querySelector('.btn-close');
        closeButton.addEventListener('click', function() {
            hideModal(modal);
            // Remove modal from DOM after it's fully hidden
            setTimeout(() => {
                modal.remove();
            }, 150);
        });
        
        // Add event listener for modal close (backdrop click, ESC key, etc.)
        modal.addEventListener('hidden.bs.modal', function() {
            // refreshApiV1Content();
            modal.remove();
        });
        
        // Also handle manual close events
        // change the event from click to mousedown with exiting the modal by accidenrt
        modal.addEventListener('mousedown', function(e) {
            debugger
            if (e.target === modal) {
                hideModal(modal);
                // Remove modal after it's fully hidden
                setTimeout(() => {
                    modal.remove();
                }, 150);
            }
        });
        
        document.body.appendChild(modal);
    }
    
    // Store the source element for later use
    if (sourceElement) {
        modal.setAttribute('data-source-element', 'true');
        // Store a reference to the source element using a unique identifier
        const sourceId = sourceElement.id || 'api-v1-' + Date.now();
        if (!sourceElement.id) {
            sourceElement.id = sourceId;
        }
        modal.setAttribute('data-source-id', sourceId);
    }
    
    // Show modal using jQuery or vanilla JS
    if (typeof $ !== 'undefined' && $.fn.modal) {
        // jQuery Bootstrap
        $(modal).modal('show');
    } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        // Bootstrap 5
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    } else {
        // Fallback - show modal manually
        modal.style.display = 'block';
        modal.classList.add('show');
        document.body.classList.add('modal-open');
        
        // Add backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
    }
    
    // Load content
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(html => {
        const modalBody = modal.querySelector('.modal-body');
        modalBody.innerHTML = html;
        
        // Set up form submission handling
        setupModalFormHandling(modal, url);
    })
    .catch(error => {
        console.error('Error loading modal content:', error);
        const modalBody = modal.querySelector('.modal-body');
        modalBody.innerHTML = '<div class="alert alert-danger">Error loading content</div>';
    });
}

// Function to refresh API v1 content
function refreshApiV1Content(targetElement = null) {
    let parentContainer;
    
    if (targetElement) {
        // If targetElement is provided, ensure it's a valid DOM element
        if (targetElement && targetElement.nodeType === Node.ELEMENT_NODE) {
            parentContainer = targetElement;
        } else {
            console.error('Invalid target element provided to refreshApiV1Content');
            return;
        }
    } else {
        // Get the first .api-v1 element
        parentContainer = document.querySelector('.api-v1');
    }
    
    if (parentContainer) {
        // Show a brief loading indicator for refresh
        const originalContent = parentContainer.innerHTML;
        parentContainer.innerHTML = `
            <div class="text-center py-3">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Обновяване...</span>
                </div>
                <div class="mt-1 text-muted small">Обновяване на съдържанието...</div>
            </div>
        `;
        
        // Load new content
        loadApiV1Content(parentContainer);
    }
}

// Function to hide modal
function hideModal(modal) {
    if (typeof $ !== 'undefined' && $.fn.modal) {
        // jQuery Bootstrap
        $(modal).modal('hide');
    } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        // Bootstrap 5
        const bootstrapModal = bootstrap.Modal.getInstance(modal);
        bootstrapModal.hide();
    } else {
        // Fallback - hide modal manually
        modal.style.display = 'none';
        modal.classList.remove('show');
        document.body.classList.remove('modal-open');
        
        // Remove backdrop
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
    }
    
    // Refresh content after modal is hidden
    // setTimeout(() => {
        // refreshApiV1Content();
    // }, 150); // Small delay to ensure modal is fully closed
}

// Function to handle form submission in modal
function setupModalFormHandling(modal, customUrl = null) {
    const form = modal.querySelector('form');
    if (!form) return;
    
    // Store customUrl in modal for later use
    if (customUrl) {
        modal.setAttribute('data-custom-url', customUrl);
    }
    
    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        // Find the submit button that was actually clicked
        let clickedSubmitButton = null;
        
        // Check if the event target is a submit button
        if (e.submitter && (e.submitter.type === 'submit' || e.submitter.tagName === 'BUTTON')) {
            clickedSubmitButton = e.submitter;
        } else {
            // Fallback: find the first submit button
            clickedSubmitButton = form.querySelector('button[type="submit"], input[type="submit"]');
        }
        
        const originalText = clickedSubmitButton ? clickedSubmitButton.innerHTML : '';
        
        // Show loading state
        if (clickedSubmitButton) {
            clickedSubmitButton.disabled = true;
            clickedSubmitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
        }
        
        // Add submit button information to form data
        if (clickedSubmitButton) {
            // Add button name if it has one
            if (clickedSubmitButton.name) {
                formData.append(clickedSubmitButton.name, clickedSubmitButton.value || '');
            }
            
            // Add button text as a special field
            formData.append('_submit_button_text', clickedSubmitButton.textContent.trim());
            
            // Add button class as identifier
            if (clickedSubmitButton.className) {
                formData.append('_submit_button_class', clickedSubmitButton.className);
            }
            
            // Add button data attributes
            const dataAttributes = clickedSubmitButton.getAttributeNames().filter(attr => attr.startsWith('data-'));
            dataAttributes.forEach(attr => {
                formData.append(attr, clickedSubmitButton.getAttribute(attr));
            });
        }
        
        // Use custom URL if provided, otherwise use form.action
        const submitUrl = customUrl || modal.getAttribute('data-custom-url') || form.action;
        
        // Store the source element ID for later use in refresh
        const sourceId = modal.getAttribute('data-source-id');
        
        fetch(submitUrl, {
            method: form.method || 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json, text/html'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                return response.text();
            }
        })
        .then(data => {
            // Handle JSON response
            if (typeof data === 'object') {
                if (data.success === 1 || data.success === true) {
                    // Success - close modal and refresh parent content
                    hideModal(modal);
                    // Remove modal after it's fully hidden
                    setTimeout(() => {
                        modal.remove();
                    }, 150);
                    // Find the correct parent API v1 container to refresh
                    const sourceId = modal.getAttribute('data-source-id');
                    let parentApiContainer = null;
                    
                    if (sourceId) {
                        parentApiContainer = document.getElementById(sourceId);
                    }
                    
                    if (parentApiContainer) {
                        refreshApiV1Content(parentApiContainer);
                    } else {
                        // Fallback to loading all API v1 content
                        loadApiV1Content();
                    }
                    
                } else {
                    // Error in JSON response
                    const modalBody = modal.querySelector('.modal-body');
                    modalBody.innerHTML = `<div class="alert alert-danger">${data.message || 'Error occurred'}</div>`;
                }
            } else {
                // Handle HTML response (form with errors)
                if (data.includes('form-error') || data.includes('alert-danger') || data.includes('form')) {
                    // Update modal content with form and errors
                    const modalBody = modal.querySelector('.modal-body');
                    modalBody.innerHTML = data;
                    setupModalFormHandling(modal, customUrl || modal.getAttribute('data-custom-url')); // Re-setup form handling
                } else {
                    // Success - close modal and refresh parent content
                    hideModal(modal);
                    // Remove modal after it's fully hidden
                    setTimeout(() => {
                        modal.remove();
                    }, 150);
                    
                }
            }
        })
        .catch(error => {
            console.error('Error submitting form:', error);
            const modalBody = modal.querySelector('.modal-body');
            modalBody.innerHTML = '<div class="alert alert-danger">Error submitting form</div>';
        })
        .finally(() => {
            // Find the correct parent API v1 container to refresh
            const sourceId = modal.getAttribute('data-source-id');
            let parentApiContainer = null;
            
            if (sourceId) {
                parentApiContainer = document.getElementById(sourceId);
            }
            
            if (parentApiContainer) {
                refreshApiV1Content(parentApiContainer);
                const triggerEventReload = parentApiContainer.getAttribute('data-trigger-event-reload');
                if (triggerEventReload) {
                    const triggerEventReloadData = parentApiContainer.getAttribute('data-trigger-event-reload-data');
                    if (triggerEventReloadData) {
                        document.dispatchEvent(new CustomEvent(triggerEventReload, {
                            detail: {
                                data: triggerEventReloadData
                            }
                        }));
                    } else {
                        document.dispatchEvent(new CustomEvent(triggerEventReload));
                    }
                }
            } else {
                // Fallback to loading all API v1 content
                loadApiV1Content();
            }
            // Restore button state
            if (clickedSubmitButton) {
                clickedSubmitButton.disabled = false;
                clickedSubmitButton.innerHTML = originalText;
            }
        });
    });
    
    // Handle all buttons in the form
    const buttons = form.querySelectorAll('button, input[type="button"], input[type="submit"], a.btn');
    
    buttons.forEach(button => {
        // Skip submit buttons (already handled above)
        if (button.type === 'submit') return;
        
        // Handle cancel/close buttons
        if (button.textContent.toLowerCase().includes('cancel') || 
            button.textContent.toLowerCase().includes('затвори') ||
            button.textContent.toLowerCase().includes('отказ') ||
            button.classList.contains('btn-secondary') ||
            button.classList.contains('btn-light')) {
            
            button.addEventListener('click', function(e) {
                e.preventDefault();
                hideModal(modal);
                // Remove modal after it's fully hidden
                setTimeout(() => {
                    modal.remove();
                }, 150);
            });
        }
        
        // Handle other action buttons (like "Generate", "Preview", etc.)
        if (button.hasAttribute('data-action') || button.hasAttribute('data-url')) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const actionUrl = button.getAttribute('data-url') || button.getAttribute('data-action');
                if (actionUrl) {
                    handleButtonAction(modal, actionUrl, button);
                }
            });
        }
    });
}

// Function to handle button actions
function handleButtonAction(modal, url, button) {
    const originalText = button.innerHTML;
    
    // Show loading state
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
    
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(html => {
        // Update modal content with new response
        const modalBody = modal.querySelector('.modal-body');
        modalBody.innerHTML = html;
        
        // Re-setup form handling for new content
        setupModalFormHandling(modal, url);
    })
    .catch(error => {
        console.error('Error handling button action:', error);
        const modalBody = modal.querySelector('.modal-body');
        modalBody.innerHTML = '<div class="alert alert-danger">Error processing action</div>';
    })
    .finally(() => {
        // Restore button state
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Load API v1 content when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    loadApiV1Content();
});

// Function to update order total price
function updateOrderTotalPrice(orderId) {
    if (!orderId) return;
    
    fetch(`/order/${orderId}/total-price`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.totalPrice !== undefined) {
            const totalPriceElement = document.getElementById('order-total-price');
            if (totalPriceElement) {
                totalPriceElement.textContent = parseFloat(data.totalPrice).toFixed(2) + ' лв.';
            }
        }
    })
    .catch(error => {
        console.error('Error updating order total price:', error);
    });
}

function updateClock() {
    const clockEl = document.getElementById('clock');
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    clockEl.textContent = `${hours}:${minutes}`;
}

// Update immediately, then every minute on the minute
updateClock();
const now = new Date();
const delay = (60 - now.getSeconds()) * 1000; // wait until next minute
setTimeout(() => {
    updateClock();
    setInterval(updateClock, 60 * 1000);
}, delay);

// Export functions for manual usage
window.loadApiV1Content = loadApiV1Content;
window.loadContentInModal = loadContentInModal;
window.refreshApiV1Content = refreshApiV1Content;
window.updateOrderTotalPrice = updateOrderTotalPrice;

document.addEventListener('order_item_list', function($event) {
    // console.log($event);
    updateOrderTotalPrice($event.detail.data);
});
