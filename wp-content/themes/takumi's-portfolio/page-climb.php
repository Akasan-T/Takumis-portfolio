<?php
/**
 * Template Name: Climb(登山モード)
 * 固定ページ「climb」用テンプレート — スクロールで富士山を登る体験ページ
 */
get_header();

$uri   = get_template_directory_uri();
$email = get_theme_mod( 'takumi_email', 'akahori.t.24kdgn@gmail.com' );
$github = get_theme_mod( 'takumi_github', 'https://github.com/Akasan-T' );
$face  = get_theme_mod( 'takumi_face' ) ?: $uri . '/assets/img/My_face.jpeg';
?>

<main class="climb">
	<!-- 3D背景(スクロールで登山) -->
	<canvas id="climb-canvas"></canvas>

	<!-- 合目ナビ -->
	<nav class="climb-nav" aria-label="ページ内ナビゲーション">
		<a href="#station-start"><span class="dot"></span><span class="label">Top</span></a>
		<a href="#station-profile"><span class="dot"></span><span class="label">Profile</span></a>
		<a href="#station-skill"><span class="dot"></span><span class="label">Skill</span></a>
		<a href="#station-work"><span class="dot"></span><span class="label">Work</span></a>
		<a href="#station-summit"><span class="dot"></span><span class="label">Contact</span></a>
	</nav>

	<!-- 麓(登山口) -->
	<section class="climb-station climb-station--start" id="station-start" data-anchor="0.03">
		<div class="climb-card climb-card--center" data-reveal>
			<p class="climb-eyebrow">Portfolio — Web Developer</p>
			<h1 class="climb-title"><?php echo esc_html( get_theme_mod( 'takumi_name_ja', '赤堀 匠海' ) ); ?><span class="en"><?php echo esc_html( get_theme_mod( 'takumi_name_en', 'Takumi Akahori' ) ); ?></span></h1>
			<p class="climb-text"><?php echo esc_html( get_theme_mod( 'takumi_top_tagline', 'フロントエンドからバックエンドまで、想いをかたちにする。' ) ); ?></p>
			<p class="climb-scroll-hint">Scroll ↓</p>
		</div>
	</section>

	<!-- 一合目: Profile -->
	<section class="climb-station" id="station-profile" data-anchor="0.25">
		<div class="climb-card" data-reveal>
			<h2 class="climb-heading">Profile<span>私について</span></h2>
			<div class="climb-card__body">
			<div class="climb-profile">
				<img src="<?php echo esc_url( $face ); ?>" alt="プロフィール写真">
				<div>
					<p class="climb-name"><?php echo esc_html( get_theme_mod( 'takumi_name_ja', '赤堀 匠海' ) ); ?><small><?php echo esc_html( get_theme_mod( 'takumi_name_en', 'Akahori Takumi' ) ); ?></small></p>
					<p class="climb-text">
						<?php echo esc_html( get_theme_mod( 'takumi_bio', '2004年岐阜県生まれ。KADOKAWAドワンゴ情報工科学院に在籍し、Web開発を学習中。産学連携プロジェクトではチームリーダーを経験。' ) ); ?>
					</p>
				</div>
			</div>
			<a class="btn" href="<?php echo esc_url( takumi_page_url( 'about' ) ); ?>">More About Me</a>
		</div>
		</div>
	</section>

	<!-- 二合目: Skill -->
	<section class="climb-station climb-station--right" id="station-skill" data-anchor="0.5">
		<div class="climb-card" data-reveal>
			<h2 class="climb-heading">Skill<span>できること</span></h2>
			<div class="climb-card__body">
			<div class="climb-skills">
				<?php foreach ( takumi_get_top_skill_icons() as $icon ) : ?>
					<img src="<?php echo esc_url( takumi_skill_icon_url( $icon ) ); ?>" alt="<?php echo esc_attr( $icon ); ?>" loading="lazy">
				<?php endforeach; ?>
			</div>
			<p class="climb-text">
				<?php echo esc_html( get_theme_mod( 'takumi_top_skill_desc', 'フロントエンドからバックエンドまで。HTML/CSSでの制作経験を軸に、Laravel・Django などのフレームワークにも挑戦中です。' ) ); ?>
			</p>
			<a class="btn btn--gold" href="<?php echo esc_url( takumi_page_url( 'about' ) ); ?>#skill">View All Skills</a>
		</div>
		</div>
	</section>

	<!-- 三合目: Work -->
	<section class="climb-station" id="station-work" data-anchor="0.75">
		<div class="climb-card" data-reveal>
			<h2 class="climb-heading">Work<span>制作実績</span></h2>
			<div class="climb-card__body">
			<div class="climb-works">
				<?php
				// 制作実績の投稿から最新3件のサムネイルを表示(なければ同梱画像)
				$featured = takumi_get_works();
				$shown    = 0;
				foreach ( $featured as $work ) {
					$thumb = get_the_post_thumbnail_url( $work->ID, 'medium_large' );
					if ( ! $thumb ) {
						continue;
					}
					printf(
						'<a href="%s"><img src="%s" alt="%s" loading="lazy"></a>',
						esc_url( takumi_page_url( 'work' ) ),
						esc_url( $thumb ),
						esc_attr( get_the_title( $work ) )
					);
					if ( ++$shown >= 3 ) {
						break;
					}
				}
				if ( ! $shown ) {
					foreach ( array( 'YLMEMORIA/YL MEMORIA.png', 'hikariwo/HiKaRiWo_LP.png', 'img/portfolio.png' ) as $img ) {
						printf(
							'<a href="%s"><img src="%s" alt="制作実績" loading="lazy"></a>',
							esc_url( takumi_page_url( 'work' ) ),
							esc_url( $uri . '/assets/img/' . $img )
						);
					}
				}
				?>
			</div>
			<p class="climb-text">
				<?php echo esc_html( get_theme_mod( 'takumi_top_work_desc', '個人制作から産学連携・実案件まで。チームリーダーとして指揮したプロジェクトも紹介しています。' ) ); ?>
			</p>
			<a class="btn" href="<?php echo esc_url( takumi_page_url( 'work' ) ); ?>">View All Works</a>
		</div>
		</div>
	</section>

	<!-- 山頂: Contact -->
	<section class="climb-station climb-station--summit" id="station-summit" data-anchor="0.98">
		<div class="climb-card climb-card--center" data-reveal>
			<h2 class="climb-heading climb-heading--center">Contact<span>お問い合わせ</span></h2>
			<div class="climb-card__body">
			<p class="climb-text">
				<?php echo wp_kses_post( get_theme_mod( 'takumi_top_contact_text', '最後までご覧いただきありがとうございました。<br>制作のご依頼・ご相談など、お気軽にご連絡ください。' ) ); ?>
			</p>
			<?php if ( shortcode_exists( 'contact-form-7' ) ) : ?>
				<div class="contact-form">
					<?php echo do_shortcode( '[contact-form-7 id="3b0857e" title="Contact form 1"]' ); ?>
				</div>
			<?php endif; ?>
			<div class="contact-links">
				<a class="btn" href="mailto:<?php echo esc_attr( $email ); ?>">Email</a>
				<a class="btn btn--gold" href="<?php echo esc_url( $github ); ?>" target="_blank" rel="noopener">GitHub</a>
			</div>
			<a class="climb-descend" href="#station-start">↑ Back to Top</a>
		</div>
		</div>
	</section>
</main>

<?php get_footer(); ?>
