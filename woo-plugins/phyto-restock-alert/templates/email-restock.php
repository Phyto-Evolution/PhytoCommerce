<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8">
<title><?php echo esc_html( $pname ); ?></title></head>
<body style="font-family:sans-serif;color:#333;max-width:600px;margin:auto;">
<h2><?php echo esc_html( $shop_name ); ?></h2>
<p><?php printf( esc_html__( 'Hi %s,', 'phyto-restock-alert' ), $name ); ?></p>
<p><?php printf( esc_html__( 'Great news! <strong>%s</strong> is back in stock.', 'phyto-restock-alert' ), $pname ); ?></p>
<?php if ( $img ) : ?><p><img src="<?php echo $img; ?>" alt="" style="max-width:200px;"></p><?php endif; ?>
<p><?php printf( esc_html__( 'Price: %s', 'phyto-restock-alert' ), $price ); ?></p>
<p><a href="<?php echo $link; ?>" style="background:#2a7a2a;color:#fff;padding:12px 24px;text-decoration:none;border-radius:4px;"><?php esc_html_e( 'Shop Now', 'phyto-restock-alert' ); ?></a></p>
<hr><p style="font-size:12px;color:#999;"><?php printf( esc_html__( 'You received this because you subscribed for restock alerts on %s.', 'phyto-restock-alert' ), esc_html( $shop_name ) ); ?></p>
</body></html>
