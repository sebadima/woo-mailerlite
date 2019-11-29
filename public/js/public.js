jQuery(document).ready(function(a) {
    var email = document.querySelector('#billing_email');
    if (email !== null) {
        email.addEventListener('blur', (event) => {
            jQuery.ajax({
                url: woo_ml_public_post.ajax_url,
                type: "post",
                data: {
                    action: "post_woo_ml_email_cookie",
                    email:email.value
                }
            })
        });
    }
});