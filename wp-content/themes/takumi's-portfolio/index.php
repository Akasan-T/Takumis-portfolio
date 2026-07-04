<?php
/**
 * 汎用フォールバックテンプレート
 */
get_header();
?>

<main>
	<section class="page-hero">
		<canvas class="stars-canvas"></canvas>
		<h1 class="page-hero__title"><?php echo is_singular() ? esc_html( get_the_title() ) : esc_html( get_bloginfo( 'name' ) ); ?></h1>
	</section>

	<section class="section" style="padding-top: 40px;">
		<div class="container">
			<?php
			if ( have_posts() ) {
				while ( have_posts() ) {
					the_post();
					?>
					<article style="margin-bottom: 56px;">
						<?php if ( ! is_singular() ) : ?>
							<h2 style="font-family: var(--font-serif); font-size: 24px; margin-bottom: 16px;">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h2>
						<?php endif; ?>
						<div style="color: var(--text-dim);"><?php the_content(); ?></div>
					</article>
					<?php
				}
			} else {
				echo '<p style="color: var(--text-dim);">コンテンツが見つかりませんでした。</p>';
			}
			?>
		</div>
	</section>
</main>

<?php get_footer(); ?>
