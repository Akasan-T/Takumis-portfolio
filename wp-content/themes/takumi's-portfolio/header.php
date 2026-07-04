<?php
/**
 * ヘッダーテンプレート
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/img/icon/logo_img.png">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Loader -->
<div class="loader">
	<p class="loader__mark"><?php bloginfo( 'name' ); ?></p>
	<div class="loader__bar"></div>
</div>

<!-- Header -->
<header class="site-header">
	<a class="site-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
	<button class="nav-toggle" aria-label="メニューを開く">
		<span></span><span></span><span></span>
	</button>
	<nav class="global-nav">
		<?php
		if ( has_nav_menu( 'global' ) ) {
			wp_nav_menu( array(
				'theme_location' => 'global',
				'container'      => false,
			) );
		} else {
			?>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" <?php echo is_front_page() ? 'class="is-current"' : ''; ?>>Top</a></li>
				<li><a href="<?php echo esc_url( takumi_page_url( 'about' ) ); ?>" <?php echo is_page( 'about' ) ? 'class="is-current"' : ''; ?>>About</a></li>
				<li><a href="<?php echo esc_url( takumi_page_url( 'work' ) ); ?>" <?php echo is_page( 'work' ) ? 'class="is-current"' : ''; ?>>Work</a></li>
				<li><a href="<?php echo esc_url( home_url( '/#station-summit' ) ); ?>">Contact</a></li>
			</ul>
			<?php
		}
		?>
	</nav>
</header>
