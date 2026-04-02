<?php
/**
 * Wholesale role management.
 *
 * @package PhytoWholesalePortal
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Phyto_WP_Roles {

	const ROLE = 'phyto_wholesale';

	public static function ensure_role() {
		if ( ! get_role( self::ROLE ) ) {
			$customer_caps = get_role( 'customer' ) ? get_role( 'customer' )->capabilities : array();
			add_role( self::ROLE, __( 'Wholesale Customer', 'phyto-wholesale-portal' ), $customer_caps );
		}
	}

	public static function is_wholesale( $user_id = null ) {
		if ( ! $user_id ) { $user_id = get_current_user_id(); }
		if ( ! $user_id ) { return false; }
		$user = get_userdata( $user_id );
		return $user && in_array( self::ROLE, (array) $user->roles, true );
	}

	public static function grant( $user_id ) {
		$user = get_userdata( absint( $user_id ) );
		if ( $user ) {
			$user->set_role( self::ROLE );
		}
	}

	public static function revoke( $user_id ) {
		$user = get_userdata( absint( $user_id ) );
		if ( $user ) {
			$user->set_role( 'customer' );
		}
	}
}
