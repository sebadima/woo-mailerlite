jQuery(document).ready(function(a) {
    var b = a("#woocommerce_mailerlite_group");
    if (0 !== b.length) {
        var c = b.next(".select2-container");
        a('<span id="woo-ml-refresh-groups" class="woo-ml-icon-refresh" data-woo-ml-refresh-groups="true"></span>').insertAfter(c);
    }
    var d = !1;
    a(document).on("click", "[data-woo-ml-refresh-groups]", function(b) {
        if (b.preventDefault(), !d) {
            var c = a(this);
            c.removeClass("error"), c.addClass("running"), d = !0, jQuery.ajax({
                url: woo_ml_post.ajax_url,
                type: "post",
                data: {
                    action: "post_woo_ml_refresh_groups"
                },
                success: function(a) {
                    a.indexOf("success") >= 0 && c.removeClass("running"), a ? location.reload() : c.addClass("error"), 
                    d = !1;
                }
            });
        }
    });
});