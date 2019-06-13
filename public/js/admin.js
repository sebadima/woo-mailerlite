jQuery(document).ready(function(a) {
    function b() {
        h -= i, k = 100 * (g - h) / g;
        var a = j.find("div");
        a.css("width", k + "%");
    }
    var c = a("#woocommerce_mailerlite_group");
    if (0 !== c.length) {
        var d = c.next(".select2-container");
        a('<span id="woo-ml-refresh-groups" class="woo-ml-icon-refresh" data-woo-ml-refresh-groups="true"></span>').insertAfter(d);
    }
    var e = !1;
    a(document).on("click", "[data-woo-ml-refresh-groups]", function(b) {
        if (b.preventDefault(), !e) {
            var c = a(this);
            c.removeClass("error"), c.addClass("running"), e = !0, jQuery.ajax({
                url: woo_ml_post.ajax_url,
                type: "post",
                data: {
                    action: "post_woo_ml_refresh_groups"
                },
                success: function(a) {
                    a.indexOf("success") >= 0 && c.removeClass("running"), a ? location.reload() : c.addClass("error"), 
                    e = !1;
                }
            });
        }
    });
    var f = !1, g = 0, h = 0, i = 0, j = a("#woo-ml-sync-untracked-orders-progress-bar"), k = 0;
    a(document).on("click", "[data-woo-ml-sync-untracked-orders]", function(c) {
        if (c.preventDefault(), !f) {
            f = !0, console.log("Synchronize untracked orders");
            var d = a(this);
            for (d.prop("disabled", !0), j.show(), g = d.data("woo-ml-untracked-orders-count"), 
            h = d.data("woo-ml-untracked-orders-left"), i = d.data("woo-ml-untracked-orders-cycle"), 
            console.log("untrackedOrdersCount >> " + g), console.log("untrackedOrdersLeft >> " + h), 
            console.log("untrackedOrdersCycle >> " + i); h > 0; ) console.log("inside the loop!"), 
            jQuery.ajax({
                url: woo_ml_post.ajax_url,
                type: "post",
                data: {
                    action: "post_woo_ml_sync_untracked_orders"
                },
                async: !1,
                success: function(a) {
                    a.indexOf("success") >= 0 && console.log("done!"), a ? (console.log("Response: True"), 
                    b()) : console.log("Response: False");
                }
            });
            console.log("loop finished!"), d.hide(), j.hide(), a("#woo-ml-sync-untracked-orders-success").show();
        }
    });
    
    var field = a("#woocommerce_mailerlite_api_key");
    a('<button id="woo-ml-validate-key" class="button-primary">Validate Key</button>').insertAfter(field);
    a(document).on("click", "#woo-ml-validate-key", function(b) {
        if (b.preventDefault(), !e) {
            var key = a("#woocommerce_mailerlite_api_key").val();
            jQuery.ajax({
                url: woo_ml_post.ajax_url,
                type:"post",
                data: {
                    action: "post_woo_ml_validate_key",
                    key:key
                },
                async: !1,
                success: function(a) {
                    location.reload()
                }
            })
        }
    });
    a('#woocommerce_mailerlite_group').select2();
    
    var cs_field = a('#woocommerce_mailerlite_consumer_secret');
    if (0 !== cs_field.length) {
        var field_desc = cs_field.next(".description");
        field_desc.closest('tr').after(
                                    '<h2>Integration Details</h2>\
                                    <p class="section-description">Customize MailerLite integration for WooCommerce</p>');
    }

    var tracking_field = a('#woocommerce_mailerlite_popups');
    
    tracking_field.closest('tr').before(
                                        '<h2>Popups</h2>\
                                        <p class="section-description">Display pop-up subscribe forms created within MailerLite</p>')
    
});