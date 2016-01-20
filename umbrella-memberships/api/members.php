<?php
/**
 * API functions relating to members.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

/**
 * Get the child members of a payee.
 *
 * @since 1.0
 *
 * @param IT_Exchange_Customer $payee
 * @param IT_Exchange_Product  $membership
 *
 * @return \ITEGMS\Relationship\Relationship[]
 */
function itegms_get_payee_members_of_product( IT_Exchange_Customer $payee, IT_Exchange_Product $membership ) {

	$query = new \ITEGMS\Purchase\Purchase_Query( array(
		'customer'     => $payee->id,
		'membership'   => $membership->ID,
		'return_value' => 'relationships'
	) );

	return $query->get_results();
}
