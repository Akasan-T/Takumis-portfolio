<?php
/**
 * フッターテンプレート
 */
?>
<!-- Footer -->
<footer class="site-footer">
	<p class="site-footer__logo"><?php bloginfo( 'name' ); ?></p>
	<nav class="site-footer__nav">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Top</a>
		<a href="<?php echo esc_url( takumi_page_url( 'about' ) ); ?>">About</a>
		<a href="<?php echo esc_url( takumi_page_url( 'work' ) ); ?>">Work</a>
	</nav>
	<p class="site-footer__copy">© <?php echo esc_html( get_theme_mod( 'takumi_name_ja', 'Takumi Akahori' ) ); ?></p>
</footer>

<button class="pagetop" aria-label="ページ上部へ戻る">↑</button>

<?php wp_footer(); ?>
</body>
</html>
