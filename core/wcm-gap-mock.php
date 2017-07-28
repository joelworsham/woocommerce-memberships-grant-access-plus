<?php
/**
 * Mocks the functions so I can modify them enough to use.
 *
 * @since {{VERSION}}
 */

defined( 'ABSPATH' ) || die();

function wcm_gap_mock_grant_access_to_membership( $limit, $offset ) {

	if ( empty( $_REQUEST['post'] ) ) {
		return;
	}

	// get the plan id
	$plan_id = isset( $_REQUEST['post'] ) ? absint( $_REQUEST['post'] ) : '';

	// get the plan and set up variables
	$plan        = wc_memberships_get_membership_plan( $plan_id );
	$redirect_to = get_edit_post_link( $plan_id, 'redirect' );
	$grant_count = 0;

	// grant access to users
	if ( $plan instanceof WC_Memberships_Membership_Plan
	     && ( $access_method = $plan->get_access_method() )
	) {

		if ( 'signup' === $access_method ) {
			// grant access to free membership to previously registered users
			// TODO restore this when background processing is ready so we don't risk customer timeouts {FN 2016-08-04}
			// $grant_count += $this->grant_access_to_free_membership_plan( $plan );
		} elseif ( 'purchase' === $access_method ) {
			// grant access to non-free memberships to users that previously purchased
			// a product that grants access to the membership plan
			$grant_count += wcm_gap_mock_grant_access_to_existing_purchases( $plan, $limit, $offset );
		}
	}

	return $grant_count;
}

function wcm_gap_mock_grant_access_to_existing_purchases( $plan, $limit, $offset ) {

	$grant_count = 0;
	$product_ids = $plan->get_product_ids();

	if ( ! empty( $product_ids ) && $plan instanceof WC_Memberships_Membership_Plan ) {
		global $wpdb;

		$valid_order_statuses_for_grant = wcm_gap_mock_get_valid_order_statuses_for_granting_access( $plan );

		foreach ( $product_ids as $product_id ) {

			$product   = wc_get_product( $product_id );
			$meta_key  = is_object( $product ) && $product->is_type( 'variation' ) ? '_variation_id' : '_product_id';
			$order_ids = $wpdb->get_col( $wpdb->prepare( "
						SELECT order_id 
						FROM {$wpdb->prefix}woocommerce_order_items 
						WHERE order_item_id 
						IN ( SELECT order_item_id FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE meta_key = %s AND meta_value = %d ) 
						AND order_item_type = 'line_item'
						LIMIT {$limit} OFFSET {$offset}
						", $meta_key, $product_id
			) );

			if ( empty( $order_ids ) ) {

				continue;
			}

			foreach ( $order_ids as $order_id ) {

				$order = wc_get_order( $order_id );

				// skip if purchase doesn't have a valid status
				if ( ! $order instanceof WC_Order
				     || ! $order->has_status( $valid_order_statuses_for_grant )
				) {

					continue;
				}

				$user_id = $order->get_user_id();

				// skip if no user id or existing purchase can't grant access or extension
				if ( ! $user_id > 0
				     || ! wcm_gap_mock_grant_access_from_existing_purchase( $user_id, $product_id, $order_id, $plan->get_id() )
				) {

					continue;
				}

				// grant access and bump counter
				if ( $plan->grant_access_from_purchase( $user_id, $product_id, $order_id ) ) {

					$grant_count ++;
				}
			}
		}
	}

	return $grant_count;
}

function wcm_gap_mock_get_valid_order_statuses_for_granting_access( $plan ) {

	if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {
		$paid_statuses = wc_get_is_paid_statuses();
	} else {
		$paid_statuses = apply_filters( 'woocommerce_order_is_paid_statuses', array( 'completed', 'processing' ) );
	}

	/**
	 * Filter the array of valid order statuses that grant access
	 *
	 * Allows to include additional custom order statuses
	 * that should grant access when the admin uses
	 * the "grant previous purchases access" action
	 *
	 * @since 1.0.0
	 *
	 * @param array $valid_order_statuses_for_grant array of order statuses
	 * @param \WC_Memberships_Membership_Plan $plan the associated membership plan object
	 */
	return (array) apply_filters( 'wc_memberships_grant_access_from_existing_purchase_order_statuses', $paid_statuses, $plan );
}

function wcm_gap_mock_grant_access_from_existing_purchase( $user_id, $product_id, $order_id, $plan_id ) {

	if ( wc_memberships_cumulative_granting_access_orders_allowed() ) {

		// if membership extensions by cumulative purchases are enabled
		// grant access if the order didn't grant access before
		$user_membership = wc_memberships_get_user_membership( $user_id, $plan_id );
		$grant_access    = ! ( $user_membership && wc_memberships_has_order_granted_access( $order_id, array( 'user_membership' => $user_membership ) ) );

	} else {

		// if instead cumulative granting access orders are disallowed,
		// grant access if user is not already a member
		$grant_access = ! wc_memberships_is_user_member( $user_id, $plan_id, false );
	}

	/**
	 * Filter whether an existing purchase of the product should grant access
	 * to the membership plan or not
	 *
	 * Allows third party code to override if a previously purchased product
	 * should retroactively grant access to a membership plan or not
	 *
	 * @since 1.0.0
	 *
	 * @param bool $grant_access Default true, grant access from existing purchase
	 * @param array $args Array of arguments connected with the access request
	 */
	$grant_access = apply_filters( 'wc_memberships_grant_access_from_existing_purchase', $grant_access, array(
		'user_id'    => $user_id,
		'product_id' => $product_id,
		'order_id'   => $order_id,
		'plan_id'    => $plan_id,
	) );

	return (bool) $grant_access;
}