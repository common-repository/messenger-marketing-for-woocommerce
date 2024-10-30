// --------------------------------------------------------------------------------
var chatchampUtils = {

    URI_PARAMS: {},

    getRandomNumber: function () {
        return Date.now() + Math.random()
    },

    initURIParams: function () {
        var query = window.location.search.substring(1).split("&");
        for (var i = 0, max = query.length; i < max; i++) {
            if (query[i] === "") // check for trailing & with no param
                continue;

            var param = query[i].split("=");
            chatchampUtils.URI_PARAMS[decodeURIComponent(param[0])] = decodeURIComponent(param[1] || "");
        }
    },

    getCookie: function (name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length === 2) return parts.pop().split(";").shift();
    },

    setCookie: function (cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    },

    getCookieThatStartsWith: function (name) {
        return document.cookie.split(';').filter(function (c) {
            return c.trim().indexOf(name) === 0;
        }).map(function (c) {
            return c.trim();
        });
    },

    removeURLParameter: function (url, parameter) {
        //prefer to use l.search if you have a location/link object
        var urlparts = url.split('?');
        if (urlparts.length >= 2) {

            var prefix = encodeURIComponent(parameter) + '=';
            var pars = urlparts[1].split(/[&;]/g);

            //reverse iteration as may be destructive
            for (var i = pars.length; i-- > 0;) {
                //idiom for string.startsWith
                if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                    pars.splice(i, 1);
                }
            }

            url = urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : "");
            return url;
        } else {
            return url;
        }
    },

    guid: function () {
        function s4() {
            return Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
        }

        return s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
    }
};

// --------------------------------------------------------------------------------
var chatchampApi = {

    matchWooCommerceSession: function (chatchampSessionId, wooCommerceSessionId) {
        jQuery.ajax({
            dataType: 'json',
            contentType: 'application/json',
            method: 'POST',
            url: chatchampSettings.apiUrl + '/woocommerce/matchSession',
            data: JSON.stringify({
                chatchampSessionId: chatchampSessionId,
                wooCommerceSessionId: wooCommerceSessionId
            })
        })
            .done(function (response) {
            })
            .fail(function () {
                console.log('matchSession api call failed.')
            });
    },

    subscribed: function (sessionId) {
        var result = false;
        jQuery.ajax({
            dataType: 'json',
            method: 'GET',
            url: chatchampSettings.apiUrl + '/subscribed',
            async: false,
            success: function (data) {
                result = data.subscribed
            },
            data: {
                facebookPageId: chatchampSettings.facebookPageId,
                chatchampSessionId: sessionId
            }
        });
        return result
    },

    sendState: function (headerTitle, ip, customerIdentifier, event, state, height) {
        var settings = {
            url: chatchampSettings.apiUrl + "/checkbox",
            type: "POST",
            contentType: "application/json",
            dataType: 'json',
            data: JSON.stringify({
                "facebookPageIdentifier": chatchampSettings.facebookPageId,
                "ip": ip,
                "event": event,
                "state": state,
                "hostname": window.location.hostname,
                "customerIdentifier": customerIdentifier,
                "headerTitle": headerTitle,
                "checkboxHeight": height
            })
        };

        jQuery.ajax(settings)
            .done(function (response) {
            })
            .fail(function () {
                console.log('Checkbox api call failed.')
            });
    }

};

// --------------------------------------------------------------------------------
var chatchampSession = {

    getSessionId: function () {
        var sessionId = chatchampUtils.URI_PARAMS.chatchampSessionId;
        if (sessionId) {
            chatchampUtils.setCookie('chatchamp_session_id', sessionId, 30)
            this.restoreWoocommerceSession()
        }
        else {
            sessionId = chatchampUtils.getCookie('chatchamp_session_id');
            if (sessionId) {
                return sessionId;
            } else {
                sessionId = chatchampUtils.guid();
                chatchampUtils.setCookie('chatchamp_session_id', sessionId, 30)
            }
        }
        return sessionId
    },

    getUserRef: function () {
        return this.getSessionId() + '_' + chatchampUtils.getRandomNumber()
    },

    getWooCommerceSessionId: function () {
        return chatchampUtils.getCookieThatStartsWith('wp_woocommerce_session')[0];
    },

    restoreWoocommerceSession: function () {
        var wooCommerceSessionId = chatchampUtils.URI_PARAMS.wooCommerceSessionId;

        if (wooCommerceSessionId) {
            wooCommerceSessionId = wooCommerceSessionId.split('=');
            chatchampUtils.setCookie(wooCommerceSessionId[0], wooCommerceSessionId[1], 30);
            window.location = chatchampUtils.removeURLParameter(chatchampUtils.removeURLParameter(window.location.href, 'wooCommerceSessionId'), 'chatchampSessionId')
        }
    }
};

// --------------------------------------------------------------------------------
var chatchampFacebook = {

    initFacebookSDK: function (fb_app_id) {
        return FB.init({
            appId: fb_app_id,
            xfbml: true,
            version: 'v2.10'
        });
    }
};

// --------------------------------------------------------------------------------
var chatchamp = {

    sessionId: undefined,
    userRef: undefined,

    startup: function () {
        chatchampUtils.initURIParams();

        this.sessionId = chatchampSession.getSessionId();
        this.wooCommerceSessionId = chatchampSession.getWooCommerceSessionId();
        this.userRef = chatchampSession.getUserRef();
        console.log('chatchamp initialized with ' + this.sessionId);

        chatchampApi.matchWooCommerceSession(this.sessionId, this.wooCommerceSessionId);

        this.loadFacebookSDK();
        this.addCheckboxes();
        this.initCheckboxes();
    },

    addCheckboxes: function () {
        const $masterDiv = jQuery('<div>');
        $masterDiv.css({"font-size": "13px", "margin-top": "10px"});

        if (chatchampApi.subscribed(this.sessionId)) {
            $masterDiv.html('You are subscribed to Facebook Messenger updates.');
        } else {
            $masterDiv.html(chatchampSettings.headerTitle);

            const $checkboxPluginDiv = jQuery('<div>');
            const settings = {
                class: 'fb-messenger-checkbox',
                messenger_app_id: chatchampSettings.facebookAppId,
                page_id: chatchampSettings.facebookPageId,
                origin: chatchampSettings.origin,
                user_ref: this.userRef,
                prechecked: 'true',
                allow_login: 'true',
                size: 'large'
            };

            $checkboxPluginDiv.attr(settings);
            $masterDiv.append($checkboxPluginDiv)
        }

        jQuery('.chatchamp_plugin').html($masterDiv);
    },

    initCheckboxes: function () {
        jQuery('.chatchamp_plugin').each(function (index, element) {
            chatchamp.initCheckboxPlugin(jQuery(element));
        });
    },

    initFacebookSDK: function () {
        var fb_app_id = chatchampSettings.facebookAppId;

        if (fb_app_id) {
            chatchampFacebook.initFacebookSDK(fb_app_id);

            FB.Event.subscribe('messenger_checkbox', function (e) {
                var height = jQuery('.fb-messenger-checkbox span').height();
                chatchampApi.sendState(chatchampSettings.headerTitle, chatchampSettings.ip, chatchampSettings.customerIdentifier, e.event, e.state, height);
                console.log(e);
                if (e.event === 'rendered') {
                    console.log("Plugin was rendered");
                } else if (e.event === 'checkbox') {
                    var checkboxState = e.state;
                    console.log("Checkbox state: " + checkboxState);
                    jQuery('#messenger_checkbox_user_checked').val(checkboxState);
                } else if (e.event === 'not_you') {
                    console.log("User clicked 'not you'");
                } else if (e.event === 'hidden') {
                    jQuery('.chatchamp_plugin').hide();
                    console.log("Plugin was hidden");
                }
            });
        }
    },

    initCheckboxPlugin: function ($element) {
        var form = $element.closest('form');
        if (form.size() === 0) {
            form = jQuery('form.cart');
        }
        if (form.size() > 0) {
            form.submit(function () {
                chatchamp.confirmOptIn();
                return true;
            });
        } else {
            var button = $element.closest('a');
            if (button.size() === 0) {
                button = jQuery('.add_to_cart_button');
            }
            if (button.size() > 0) {
                button.on("click", function () {
                    chatchamp.confirmOptIn();
                })
            } else {
                console.log('No submit button found.');
            }
        }
    },

    loadFacebookSDK: function () {
        var oldFacebookSDK = window.fbAsyncInit;
        window.fbAsyncInit = function () {
            chatchamp.initFacebookSDK();
            if (typeof oldFacebookSDK === 'function') {
                // oldFacebookSDK();
            }
        };

        (function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {
                return;
            }
            js = d.createElement(s);
            js.id = id;
            js.src = "//connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    },

    getProductId: function () {
        const product = jQuery('.product').attr('id');
        if (product) {
            const match = product.match(/product-([0-9]+)/);
            if (match) {
                return match[1]
            }
        }
        return ''
    },

    getEvent: function () {
        return jQuery('.chatchamp_plugin').data('event')
    },

    confirmOptIn: function () {
        const event = this.getEvent();
        console.log('Confirmed login for ' + this.sessionId + ' (' + event + ')');
        FB.AppEvents.logEvent('MessengerCheckboxUserConfirmation', null, {
            'app_id': chatchampSettings.facebookAppId,
            'page_id': chatchampSettings.facebookPageId,
            'user_ref': this.userRef,
            'ref': JSON.stringify({
                customerIdentifier: chatchampSettings.customerIdentifier,
                event: event,
                shoppingSystem: 'woocommerce',
                productId: chatchamp.getProductId(),
                chatchampSessionId: this.sessionId
            })
        });
    }
};

jQuery(document).ready(function () {
    chatchamp.startup();
});