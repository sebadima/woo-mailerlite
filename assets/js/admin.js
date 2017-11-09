jQuery(document).ready(function ($) {

    /**
     * Output refresh groups icon
     */
    var settingGroups = $( '#woocommerce_mailerlite_group');

    if ( settingGroups.length !== 0 ) {

        var settingGroupsSelectContainer = settingGroups.next('.select2-container');

        $( '<span id="woo-ml-refresh-groups" class="woo-ml-icon-refresh" data-woo-ml-refresh-groups="true"></span>' ).insertAfter( settingGroupsSelectContainer );
    }

    /**
     * Handle refresh groups
     */
    var refreshGroupsRunning = false;

    $(document).on('click', '[data-woo-ml-refresh-groups]', function (event) {

        event.preventDefault();

        if ( refreshGroupsRunning ) // don't do anything if an AJAX request is running
            return;

        var refreshIcon = $(this);

        refreshIcon.removeClass('error');
        refreshIcon.addClass('running');
        refreshGroupsRunning = true;

        // Request
        jQuery.ajax({
            url: woo_ml_post.ajax_url,
            type: 'post',
            data: {
                action: 'post_woo_ml_refresh_groups'
            },
            success: function (response) {

                // Hide form if success
                if ( response.indexOf( "success" ) >= 0 ) {
                    //console.log('done!');
                    refreshIcon.removeClass('running');
                }

                if ( response ) {
                    // Redirect page in order to show refreshed list
                    location.reload();
                } else {
                    // Something went wrong
                    refreshIcon.addClass('error');
                }

                refreshGroupsRunning = false;
            }

        });
    });

});