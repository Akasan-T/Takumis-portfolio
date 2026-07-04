<?php
/**
 * Template Name: About(私について)
 * 固定ページ「about」用テンプレート
 */
get_header();

$uri     = get_template_directory_uri();
$name_ja = get_theme_mod( 'takumi_name_ja', '赤堀 匠海' );
$name_en = get_theme_mod( 'takumi_name_en', 'Akahori Takumi' );
$motto   = get_theme_mod( 'takumi_motto', 'Behind every smile lies effort' );
$github  = get_theme_mod( 'takumi_github', 'https://github.com/Akasan-T' );
$x_url   = get_theme_mod( 'takumi_x', 'https://x.com/hori_hori_ak' );
$face    = get_theme_mod( 'takumi_face', $uri . '/assets/img/My_face.jpeg' );

// スキル・経歴は管理画面の「スキル」「経歴」から編集(投稿がなければ既定値)
$skills   = takumi_get_skills_data();
$timeline = takumi_get_career_data();
?>

<main>
	<section class="page-hero">
		<canvas class="stars-canvas"></canvas>
		<h1 class="page-hero__title">About</h1>
		<p class="page-hero__sub">私について</p>
	</section>

	<!-- Profile -->
	<section class="section" id="profile">
		<div class="container">
			<div class="profile-grid">
				<div class="profile-photo" data-reveal>
					<img src="<?php echo esc_url( $face ); ?>" alt="<?php echo esc_attr( $name_ja ); ?>の写真">
				</div>
				<div class="profile-body" data-reveal>
					<p class="name-ja"><?php echo esc_html( $name_ja ); ?></p>
					<p class="name-en"><?php echo esc_html( $name_en ); ?></p>
					<p class="motto"><?php echo esc_html( $motto ); ?></p>
					<div class="bio">
						<?php
						// 固定ページ本文を自己紹介として表示
						while ( have_posts() ) {
							the_post();
							the_content();
						}
						?>
					</div>
					<div class="profile-sns">
						<a href="<?php echo esc_url( $github ); ?>" target="_blank" rel="noopener" aria-label="GitHub"><img src="<?php echo esc_url( $uri ); ?>/assets/img/github-brands.svg" alt="GitHub"></a>
						<a href="<?php echo esc_url( $x_url ); ?>" target="_blank" rel="noopener" aria-label="X"><img src="<?php echo esc_url( $uri ); ?>/assets/img/x-solid.svg" alt="X"></a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Skill -->
	<section class="section section--alt" id="skill">
		<div class="container">
			<div class="section-head" data-reveal>
				<h2 class="section-head__en">Skill</h2>
				<p class="section-head__ja">スキル</p>
			</div>
			<div class="skill-grid">
				<?php foreach ( $skills as $skill ) : ?>
					<div class="skill-card" data-reveal>
						<img src="https://skillicons.dev/icons?i=<?php echo esc_attr( $skill[0] ); ?>" alt="<?php echo esc_attr( $skill[1] ); ?>" loading="lazy">
						<div class="skill-card__body">
							<div class="skill-card__head"><h3><?php echo esc_html( $skill[1] ); ?></h3><span><?php echo esc_html( $skill[2] ); ?></span></div>
							<div class="skill-bar"><span class="fill" data-width="<?php echo esc_attr( $skill[3] ); ?>"></span></div>
							<p class="skill-card__note"><?php echo esc_html( $skill[4] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- Career -->
	<section class="section" id="career">
		<div class="container">
			<div class="section-head" data-reveal>
				<h2 class="section-head__en">Career</h2>
				<p class="section-head__ja">経歴</p>
			</div>
			<ul class="timeline">
				<?php foreach ( $timeline as $item ) : ?>
					<li data-reveal>
						<p class="timeline__date"><?php echo esc_html( $item[0] ); ?></p>
						<div class="timeline__body">
							<h3><?php echo esc_html( $item[1] ); ?></h3>
							<p><?php echo esc_html( $item[2] ); ?></p>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<!-- CTA -->
	<section class="section section--alt">
		<div class="container contact-box">
			<p data-reveal>制作実績もぜひご覧ください。</p>
			<div class="contact-links" data-reveal>
				<a class="btn" href="<?php echo esc_url( takumi_page_url( 'work' ) ); ?>">View Works</a>
				<a class="btn btn--gold" href="<?php echo esc_url( home_url( '/#contact' ) ); ?>">Contact</a>
			</div>
		</div>
	</section>
</main>

<?php get_footer(); ?>
