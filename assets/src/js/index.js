const data = WCM_GAP;
const l10n = data['l10n'];

/**
 * Initialize the system.
 *
 * @since {{VERSION}}
 */
const initialize = () => {

    const $actionButton = document.getElementById('grant-access');

    if ( !$actionButton ) {

        return;
    }

    $actionButton.addEventListener('click', handleActionClick);
}

/**
 * Fires on clicking the main action button.
 *
 * @since {{VERSION}}
 *
 * @param event
 */
const handleActionClick = (event) => {

    event.preventDefault();

    if ( confirm(l10n['confirmGrantAccess']) ) {

        runImport();
    }

    return false;
}

/**
 * Runs the import.
 *
 * @since {{VERSION}}
 */
const runImport = () => {

    window.onbeforeunload = function(){
        return 'ARE YOU SURE? The import will be cancelled and data could be corrupt.';
    };

    const $modal = jQuery('#wcm-gap-modal');
    const totalOrders   = parseInt(data['totalOrders']);
    const limit         = 10;
    let processedOrders = 0;
    let grantCount      = 0;

    $modal.show();

    const runImportInner = () => {

        jQuery.ajax({
            url: ajaxurl,
            method: 'post',
            data: {
                action: 'wcm_gap_grant_access_run',
                wcm_gap_ajax: '1',
                offset: processedOrders,
                limit: limit,
                post: data['post'],
                _wpnonce: data['nonce'],
            },
            success: (response) => {

                processedOrders = processedOrders + limit;
                grantCount      = grantCount + parseInt(response.data.grant_count);

                updateProgressBar(((processedOrders / totalOrders) * 100).toFixed(2));
                updateTotalGranted(grantCount);

                if ( processedOrders < totalOrders ) {

                    runImportInner();

                } else {

                    const $finished = jQuery('.wcm-gap-modal-finished');

                    $finished.show();
                }
            }
        });
    }

    runImportInner();
}

/**
 * Updates the progress bar.
 *
 * @since {{VERSION}}
 *
 * @var {int} progress
 */
const updateProgressBar = (progress) => {

    const $bar        = jQuery('.wcm-gap-modal-progress-bar');
    const $percentage = jQuery('.wcm-gap-modal-progress-percentage');

    $bar.css('width', progress + '%');
    $percentage.html(progress + '%');
}

/**
 * Updates the total granted notice.
 *
 * @since {{VERSION}}
 *
 * @var {int} total
 */
const updateTotalGranted = (total) => {

    const $granted = jQuery('.wcm-gap-modal-total-granted');

    $granted.html(total);
}

// Initialize after DOM ready
jQuery(initialize);

// Must use jQuery to remove this handler
jQuery(function () {
    jQuery('#grant-access').off('click');
});