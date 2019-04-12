window.onload = function() {
    var email = document.querySelector('#billing_email');
    if (email !== null) {
        email.addEventListener('focus', (event) => {
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
}