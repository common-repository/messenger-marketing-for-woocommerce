var chatchampApi = {

    setup: function (apiKey, apiSecret) {
        jQuery.ajax({
            method: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            url: chatchamp_api_settings.apiUrl + '/woocommerce/setup',
            data: JSON.stringify({
                chatchampId: chatchamp_api_settings.chatchampId,
                shopUrl: chatchamp_api_settings.shopUrl,
                cartUrl: chatchamp_api_settings.cartUrl,
                pluginVersion: chatchamp_api_settings.pluginVersion,
                apiKey: apiKey,
                apiSecret: apiSecret
            }),
            success: function (response) {
                if (response.success) {
                    jQuery.ajax({
                        method: 'POST',
                        dataType: 'json',
                        url: chatchamp_api_keys.ajax_url,
                        data: {
                            action: 'chatchamp_set_setup_successful'
                        }
                    });
                }
            }
        })
    },

    createApiKey: function () {
        jQuery.ajax({
            method: 'POST',
            dataType: 'json',
            url: chatchamp_api_keys.ajax_url,
            data: {
                action: 'woocommerce_update_api_key',
                security: chatchamp_api_keys.update_api_nonce,
                key_id: 0,
                description: 'chatchamp api user',
                user: chatchamp_api_keys.user_id,
                permissions: 'read'
            },
            success: function (response) {
                if (response.success) {
                    chatchampApi.setup(response.data.consumer_key, response.data.consumer_secret);
                }
            }
        })
    }
};

jQuery(document).ready(function () {
    if (chatchamp_api_settings.chatchampId.trim()) {
        if (!chatchamp_api_settings.setupSuccessful) {
            chatchampApi.createApiKey()
        }
    }
});