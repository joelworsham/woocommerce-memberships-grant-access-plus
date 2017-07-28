<?php
/**
 * Loading modal HTML.
 *
 * @since {{VERSION}}
 *
 * @var string $redirect_to
 */

defined( 'ABSPATH' ) || die();
?>

<div id="wcm-gap-modal">
    <div class="wcm-gap-modal-container">
        <h2>Granting Access <span class="spinner is-active"></span></h2>
        <p>
            Note: This may take some time. Please <strong>DO NOT LEAVE THIS PAGE</strong>.
        </p>

        <div class="notice updated inline">
            <p>
                <strong class="wcm-gap-modal-total-granted">0</strong> users granted membership access.
            </p>
        </div>

        <div class="wcm-gap-modal-progress">
            <span class="wcm-gap-modal-progress-bar"></span>
            <span class="wcm-gap-modal-progress-percentage">0%</span>
        </div>

        <p class="wcm-gap-modal-finished">
            <a href="<?php echo $redirect_to; ?>" class="button">
                Finish
            </a>
        </p>
    </div>
</div>