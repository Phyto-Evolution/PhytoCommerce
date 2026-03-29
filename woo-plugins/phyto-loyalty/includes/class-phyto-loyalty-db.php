<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Phyto_Loyalty_DB {

    public static function install(): void {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}phyto_loyalty_account` (
            `id_loyalty`      BIGINT(20) NOT NULL AUTO_INCREMENT,
            `user_id`         BIGINT(20) NOT NULL,
            `points_balance`  INT(11)    NOT NULL DEFAULT 0,
            `points_lifetime` INT(11)    NOT NULL DEFAULT 0,
            `points_redeemed` INT(11)    NOT NULL DEFAULT 0,
            `tier`            VARCHAR(20) NOT NULL DEFAULT 'seed',
            `date_add`        DATETIME   DEFAULT NULL,
            `date_upd`        DATETIME   DEFAULT NULL,
            PRIMARY KEY (`id_loyalty`),
            UNIQUE KEY `user_id` (`user_id`)
        ) $charset;" );

        dbDelta( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}phyto_loyalty_transaction` (
            `id_transaction` BIGINT(20)  NOT NULL AUTO_INCREMENT,
            `user_id`        BIGINT(20)  NOT NULL,
            `order_id`       BIGINT(20)  NOT NULL DEFAULT 0,
            `type`           VARCHAR(20) NOT NULL,
            `points`         INT(11)     NOT NULL,
            `balance_after`  INT(11)     NOT NULL,
            `note`           VARCHAR(255) DEFAULT NULL,
            `date_add`       DATETIME    DEFAULT NULL,
            PRIMARY KEY (`id_transaction`),
            KEY `idx_user_date` (`user_id`, `date_add`)
        ) $charset;" );

        add_option( 'phyto_loyalty_settings', [
            'earn_rate'       => 1,    // INR per point
            'redeem_rate'     => 1,    // points per INR discount
            'min_redeem'      => 100,  // minimum points to redeem
            'max_redeem_pct'  => 20,   // max % of cart value
            'expiry_days'     => 365,
            'enabled'         => 1,
            'tiers'           => [
                [ 'name' => 'seed',   'min_lifetime' => 0,     'multiplier' => 1.0 ],
                [ 'name' => 'sprout', 'min_lifetime' => 500,   'multiplier' => 1.2 ],
                [ 'name' => 'bloom',  'min_lifetime' => 2000,  'multiplier' => 1.5 ],
                [ 'name' => 'rare',   'min_lifetime' => 10000, 'multiplier' => 2.0 ],
            ],
        ] );
    }

    public static function get_account( int $user_id ): ?object {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM `{$wpdb->prefix}phyto_loyalty_account` WHERE user_id=%d",
            $user_id
        ) ) ?: null;
    }

    public static function ensure_account( int $user_id ): object {
        $account = self::get_account( $user_id );
        if ( ! $account ) {
            global $wpdb;
            $wpdb->insert( $wpdb->prefix . 'phyto_loyalty_account', [
                'user_id'  => $user_id,
                'date_add' => current_time( 'mysql' ),
                'date_upd' => current_time( 'mysql' ),
            ] );
            $account = self::get_account( $user_id );
        }
        return $account;
    }

    public static function add_transaction( int $user_id, string $type, int $points, int $order_id = 0, string $note = '' ): bool {
        global $wpdb;
        $account       = self::ensure_account( $user_id );
        $balance_after = $account->points_balance + $points;

        $lifetime = $type === 'earn' ? $account->points_lifetime + $points : $account->points_lifetime;
        $redeemed = $type === 'redeem' ? $account->points_redeemed + abs( $points ) : $account->points_redeemed;

        $wpdb->insert( $wpdb->prefix . 'phyto_loyalty_transaction', [
            'user_id'       => $user_id,
            'order_id'      => $order_id,
            'type'          => $type,
            'points'        => $points,
            'balance_after' => $balance_after,
            'note'          => $note,
            'date_add'      => current_time( 'mysql' ),
        ] );

        $tier = self::calculate_tier( $lifetime );

        $wpdb->update( $wpdb->prefix . 'phyto_loyalty_account', [
            'points_balance'  => $balance_after,
            'points_lifetime' => $lifetime,
            'points_redeemed' => $redeemed,
            'tier'            => $tier,
            'date_upd'        => current_time( 'mysql' ),
        ], [ 'user_id' => $user_id ] );

        return true;
    }

    public static function calculate_tier( int $lifetime_points ): string {
        $settings = get_option( 'phyto_loyalty_settings', [] );
        $tiers    = $settings['tiers'] ?? [];
        $tier     = 'seed';
        foreach ( $tiers as $t ) {
            if ( $lifetime_points >= $t['min_lifetime'] ) $tier = $t['name'];
        }
        return $tier;
    }

    public static function get_transactions( int $user_id, int $limit = 50 ): array {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM `{$wpdb->prefix}phyto_loyalty_transaction` WHERE user_id=%d ORDER BY date_add DESC LIMIT %d",
            $user_id, $limit
        ) ) ?: [];
    }
}
