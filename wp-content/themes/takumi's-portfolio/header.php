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
	<?php
	$takumi_seo_desc  = get_theme_mod( 'takumi_seo_description', '赤堀匠海(Akahori Takumi)のポートフォリオサイト。Web制作のスキル・経歴・制作実績を紹介しています。' );
	$takumi_seo_image = get_theme_mod( 'takumi_og_image' ) ?: get_template_directory_uri() . '/assets/img/img/portfolio.png';
	$takumi_seo_url   = home_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) ) );
	?>
	<meta name="description" content="<?php echo esc_attr( $takumi_seo_desc ); ?>">
	<meta property="og:type" content="website">
	<meta property="og:site_name" content="<?php bloginfo( 'name' ); ?>">
	<meta property="og:title" content="<?php echo esc_attr( wp_get_document_title() ); ?>">
	<meta property="og:description" content="<?php echo esc_attr( $takumi_seo_desc ); ?>">
	<meta property="og:url" content="<?php echo esc_url( $takumi_seo_url ); ?>">
	<meta property="og:image" content="<?php echo esc_url( $takumi_seo_image ); ?>">
	<meta name="twitter:card" content="summary_large_image">
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
