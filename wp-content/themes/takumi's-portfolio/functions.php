<?php
/**
 * Takumi Portfolio — テーマ機能
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TAKUMI_VERSION', '1.0.0' );

/* ============================================================
   テーマサポート
   ============================================================ */
add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'automatic-feed-links' );

	register_nav_menus( array(
		'global' => 'グローバルナビゲーション',
	) );
} );

/* ============================================================
   CSS / JS の読み込み
   ============================================================ */
add_action( 'wp_enqueue_scripts', function () {
	$uri = get_template_directory_uri();

	wp_enqueue_style( 'destyle', 'https://cdn.jsdelivr.net/npm/destyle.css@3.0.2/destyle.css', array(), '3.0.2' );
	wp_enqueue_style(
		'takumi-fonts',
		'https://fonts.googleapis.com/css2?family=Zen+Old+Mincho:wght@400;600&family=Zen+Kaku+Gothic+New:wght@400;500;700&family=Marcellus&family=Montserrat:wght@400;500;600&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'takumi-style', $uri . '/assets/css/style.css', array( 'destyle' ), TAKUMI_VERSION );

	wp_enqueue_script( 'gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js', array(), '3.12.2', true );
	wp_enqueue_script( 'gsap-scrolltrigger', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js', array( 'gsap' ), '3.12.2', true );
	wp_enqueue_script( 'takumi-main', $uri . '/assets/js/main.js', array( 'gsap-scrolltrigger' ), TAKUMI_VERSION, true );

	// 作品データ・フィルタ・詳細パネル
	wp_enqueue_script( 'takumi-works', $uri . '/assets/js/works.js', array(), TAKUMI_VERSION, true );
	wp_add_inline_script(
		'takumi-works',
		'window.TAKUMI_BASE = ' . wp_json_encode( $uri . '/assets/' ) . ';'
		. 'window.TAKUMI_WORK_URL = ' . wp_json_encode( takumi_page_url( 'work' ) ) . ';',
		'before'
	);

	// Three.js 星空背景(全ページ)
	wp_enqueue_script( 'takumi-three-stars', $uri . '/assets/js/three-stars.js', array(), TAKUMI_VERSION, true );

	// Three.js 登山モード(フロントページ / climbテンプレート)
	if ( is_front_page() || is_page_template( 'page-climb.php' ) || is_page( 'climb' ) ) {
		wp_enqueue_script( 'takumi-three-climb', $uri . '/assets/js/three-climb.js', array(), TAKUMI_VERSION, true );
	}
} );

// Three.js 関連は ES Modules として読み込む
add_filter( 'script_loader_tag', function ( $tag, $handle ) {
	if ( in_array( $handle, array( 'takumi-three-hero', 'takumi-three-stars', 'takumi-three-climb' ), true ) ) {
		$tag = str_replace( '<script ', '<script type="module" ', $tag );
	}
	return $tag;
}, 10, 2 );

/* ============================================================
   ユーティリティ
   ============================================================ */

/**
 * スラッグからページURLを取得(なければトップへのアンカー)
 */
function takumi_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : home_url( '/#' . $slug );
}

/* ============================================================
   カスタマイザー(プロフィール設定)
   ============================================================ */
add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_section( 'takumi_profile', array(
		'title'    => 'プロフィール設定',
		'priority' => 30,
	) );

	$fields = array(
		'takumi_name_ja' => array( '名前(日本語)', '赤堀 匠海' ),
		'takumi_name_en' => array( '名前(ローマ字)', 'Akahori Takumi' ),
		'takumi_motto'   => array( 'モットー', 'Behind every smile lies effort' ),
		'takumi_email'   => array( 'メールアドレス', 'akahori.t.24kdgn@gmail.com' ),
		'takumi_github'  => array( 'GitHub URL', 'https://github.com/Akasan-T' ),
		'takumi_x'       => array( 'X (Twitter) URL', 'https://x.com/hori_hori_ak' ),
	);
	foreach ( $fields as $key => $conf ) {
		$wp_customize->add_setting( $key, array(
			'default'           => $conf[1],
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( $key, array(
			'label'   => $conf[0],
			'section' => 'takumi_profile',
			'type'    => 'text',
		) );
	}

	$wp_customize->add_setting( 'takumi_bio', array(
		'default'           => '2004年岐阜県生まれ。KADOKAWAドワンゴ情報工科学院と産業能率大学に在籍し、Web開発やマーケティングを学習中。フロントエンドからバックエンドまで幅広い技術を習得し、産学連携プロジェクトではチームリーダーとしてWebサイト開発を経験。実践力と企画力を活かし、多角的に活躍できる人材を目指しています。',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'takumi_bio', array(
		'label'   => '自己紹介文',
		'section' => 'takumi_profile',
		'type'    => 'textarea',
	) );

	$wp_customize->add_setting( 'takumi_face', array(
		'default'           => get_template_directory_uri() . '/assets/img/My_face.jpeg',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'takumi_face', array(
		'label'   => 'プロフィール写真',
		'section' => 'takumi_profile',
	) ) );
} );

/* ============================================================
   カスタム投稿タイプ: 制作実績 (works)
   ============================================================ */
add_action( 'init', function () {
	register_post_type( 'works', array(
		'labels' => array(
			'name'          => '制作実績',
			'singular_name' => '制作実績',
			'add_new_item'  => '制作実績を追加',
			'edit_item'     => '制作実績を編集',
		),
		'public'       => true,
		'has_archive'  => false,
		'menu_icon'    => 'dashicons-portfolio',
		'menu_position'=> 5,
		'supports'     => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		'show_in_rest' => true,
	) );
} );

/* ---------- 制作実績メタボックス ---------- */
const TAKUMI_WORK_FIELDS = array(
	'meta'  => array( 'サブタイトル', '例: 個人制作・2025' ),
	'year'  => array( '制作年', '例: 2025' ),
	'type'  => array( '種別(カンマ区切り)', 'front / back / design' ),
	'tech'  => array( '技術(カンマ区切り)', '例: html/css,js,php' ),
	'role'  => array( '担当', '例: デザイン・コーディング全て' ),
	'url'   => array( '公開URL', '空欄可' ),
	'github'=> array( 'GitHub URL', '空欄可' ),
	'icons' => array( 'スキルアイコン(カンマ区切り)', 'skillicons.dev のID。例: html,css,js' ),
);

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'takumi_work_meta', '実績情報', function ( $post ) {
		wp_nonce_field( 'takumi_work_meta', 'takumi_work_meta_nonce' );
		foreach ( TAKUMI_WORK_FIELDS as $key => $conf ) {
			$value = get_post_meta( $post->ID, '_takumi_' . $key, true );
			printf(
				'<p><label for="takumi_%1$s"><strong>%2$s</strong><br><small>%3$s</small></label><br>' .
				'<input type="text" id="takumi_%1$s" name="takumi_%1$s" value="%4$s" style="width:100%%"></p>',
				esc_attr( $key ),
				esc_html( $conf[0] ),
				esc_html( $conf[1] ),
				esc_attr( $value )
			);
		}
		echo '<p><small>ギャラリー画像はアイキャッチ画像と、本文に挿入した画像が使われます。</small></p>';
	}, 'works', 'normal', 'high' );
} );

add_action( 'save_post_works', function ( $post_id ) {
	if ( ! isset( $_POST['takumi_work_meta_nonce'] ) ||
		! wp_verify_nonce( $_POST['takumi_work_meta_nonce'], 'takumi_work_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( array_keys( TAKUMI_WORK_FIELDS ) as $key ) {
		if ( isset( $_POST[ 'takumi_' . $key ] ) ) {
			update_post_meta( $post_id, '_takumi_' . $key, sanitize_text_field( wp_unslash( $_POST[ 'takumi_' . $key ] ) ) );
		}
	}
} );

/* ---------- 制作実績の取得・カード出力 ---------- */

/**
 * 制作実績のカードを出力する
 */
function takumi_render_work_card( $post ) {
	$id     = $post->ID;
	$meta   = fn( $key ) => get_post_meta( $id, '_takumi_' . $key, true );
	$thumb  = get_the_post_thumbnail_url( $id, 'large' );
	$techs  = array_filter( array_map( 'trim', explode( ',', (string) $meta( 'tech' ) ) ) );
	$types  = array_filter( array_map( 'trim', explode( ',', (string) $meta( 'type' ) ) ) );
	$icons  = array_filter( array_map( 'trim', explode( ',', (string) $meta( 'icons' ) ) ) );

	// 本文内の画像 + アイキャッチをギャラリーに
	$images = $thumb ? array( $thumb ) : array();
	if ( preg_match_all( '/<img[^>]+src="([^"]+)"/', $post->post_content, $m ) ) {
		$images = array_values( array_unique( array_merge( $images, $m[1] ) ) );
	}
	?>
	<article class="work-row"
		data-year="<?php echo esc_attr( $meta( 'year' ) ); ?>"
		data-tech="<?php echo esc_attr( implode( ',', $techs ) ); ?>"
		data-type="<?php echo esc_attr( implode( ',', $types ) ); ?>">
		<div class="work-row__gallery">
			<?php foreach ( $images as $src ) : ?>
				<img src="<?php echo esc_url( $src ); ?>" alt="<?php the_title_attribute( array( 'post' => $id ) ); ?>" loading="lazy">
			<?php endforeach; ?>
		</div>
		<div class="work-row__body">
			<h3><?php echo esc_html( get_the_title( $id ) ); ?></h3>
			<p class="work-row__meta"><?php echo esc_html( $meta( 'meta' ) ); ?></p>
			<?php if ( $meta( 'role' ) ) : ?>
				<p class="work-row__role"><strong>担当:</strong> <?php echo esc_html( $meta( 'role' ) ); ?></p>
			<?php endif; ?>
			<div class="work-row__desc"><?php echo wp_kses_post( wpautop( $post->post_content ) ); ?></div>
			<div class="work-row__tags">
				<?php foreach ( $techs as $tech ) : ?>
					<span><?php echo esc_html( $tech ); ?></span>
				<?php endforeach; ?>
			</div>
			<?php if ( $icons ) : ?>
				<div class="work-row__icons">
					<?php foreach ( $icons as $icon ) : ?>
						<img src="https://skillicons.dev/icons?i=<?php echo esc_attr( $icon ); ?>" alt="<?php echo esc_attr( $icon ); ?>" loading="lazy">
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<div class="work-row__links">
				<?php if ( $meta( 'url' ) ) : ?>
					<a class="btn" href="<?php echo esc_url( $meta( 'url' ) ); ?>" target="_blank" rel="noopener">Visit Site</a>
				<?php endif; ?>
				<?php if ( $meta( 'github' ) ) : ?>
					<a class="btn btn--gold" href="<?php echo esc_url( $meta( 'github' ) ); ?>" target="_blank" rel="noopener">GitHub</a>
				<?php endif; ?>
			</div>
		</div>
	</article>
	<?php
}

/**
 * 制作実績の投稿一覧を取得
 */
function takumi_get_works() {
	return get_posts( array(
		'post_type'      => 'works',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order date',
		'order'          => 'DESC',
	) );
}
