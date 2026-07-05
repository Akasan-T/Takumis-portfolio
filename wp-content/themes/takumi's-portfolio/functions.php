<?php
/**
 * Takumi Portfolio — テーマ機能
 */

show_admin_bar(false);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TAKUMI_VERSION', '1.0.8' );

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
		wp_add_inline_script(
			'takumi-three-climb',
			'window.TAKUMI_SKILLS = ' . wp_json_encode( takumi_get_top_skills() ) . ';'
			. 'window.TAKUMI_SKILL_ICON_BASE = ' . wp_json_encode( $uri . '/assets/img/skills/' ) . ';',
			'before'
		);
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
		'default'           => '2004年岐阜県生まれ。KADOKAWAドワンゴ情報工科学院に在籍し、Web開発を学習中。産学連携プロジェクトではチームリーダーを経験。',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'takumi_bio', array(
		'label'       => '自己紹介文(トップページ用・短め)',
		'description' => 'トップページ/登山モードのProfileセクションに表示されます。',
		'section'     => 'takumi_profile',
		'type'        => 'textarea',
	) );

	$wp_customize->add_setting( 'takumi_face', array(
		// NOTE: テーマフォルダ名に含まれるアポストロフィがURLエンコードされると "%27s" となり、
		// get_theme_mod() の sprintf プレースホルダー検出に誤って一致し値が壊れるため、
		// ここでは空文字をデフォルトにし、実際のフォールバックは呼び出し側のPHPで行う。
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'takumi_face', array(
		'label'   => 'プロフィール写真',
		'section' => 'takumi_profile',
	) ) );

	/* ---------- SEO / OGP ---------- */
	$wp_customize->add_section( 'takumi_seo', array(
		'title'    => 'SEO / OGP設定',
		'priority' => 32,
	) );

	$wp_customize->add_setting( 'takumi_seo_description', array(
		'default'           => '赤堀匠海(Akahori Takumi)のポートフォリオサイト。Web制作のスキル・経歴・制作実績を紹介しています。',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'takumi_seo_description', array(
		'label'       => 'サイトの説明文(meta description / OGP用)',
		'description' => '検索結果やSNSでのシェア時に表示される説明文です。',
		'section'     => 'takumi_seo',
		'type'        => 'textarea',
	) );

	$wp_customize->add_setting( 'takumi_og_image', array(
		// takumi_face と同じ理由でデフォルトは空文字にする(下の header.php 側でフォールバック)
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'takumi_og_image', array(
		'label'       => 'OGP画像(SNSシェア時のサムネイル)',
		'description' => '推奨サイズ: 1200×630px程度',
		'section'     => 'takumi_seo',
	) ) );

	/* ---------- トップページ文言 ---------- */
	$wp_customize->add_section( 'takumi_top_texts', array(
		'title'    => 'トップページ文言設定',
		'priority' => 31,
	) );

	$wp_customize->add_setting( 'takumi_top_tagline', array(
		'default'           => 'フロントエンドからバックエンドまで、想いをかたちにする。',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'takumi_top_tagline', array(
		'label'   => 'キャッチコピー(麓/Topセクション)',
		'section' => 'takumi_top_texts',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'takumi_top_skill_desc', array(
		'default'           => 'フロントエンドからバックエンドまで。HTML/CSSでの制作経験を軸に、Laravel・Django などのフレームワークにも挑戦中です。',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'takumi_top_skill_desc', array(
		'label'   => 'Skillセクションの説明文',
		'section' => 'takumi_top_texts',
		'type'    => 'textarea',
	) );

	$wp_customize->add_setting( 'takumi_top_work_desc', array(
		'default'           => '個人制作から産学連携・実案件まで。チームリーダーとして指揮したプロジェクトも紹介しています。',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'takumi_top_work_desc', array(
		'label'   => 'Workセクションの説明文',
		'section' => 'takumi_top_texts',
		'type'    => 'textarea',
	) );

	$wp_customize->add_setting( 'takumi_top_contact_text', array(
		'default'           => '最後までご覧いただきありがとうございました。<br>制作のご依頼・ご相談など、お気軽にご連絡ください。',
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'takumi_top_contact_text', array(
		'label'       => 'Contactセクションの案内文',
		'description' => '改行したい箇所には &lt;br&gt; を挿入できます。',
		'section'     => 'takumi_top_texts',
		'type'        => 'textarea',
	) );
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

	register_post_type( 'skill', array(
		'labels' => array(
			'name'          => 'スキル',
			'singular_name' => 'スキル',
			'add_new_item'  => 'スキルを追加',
			'edit_item'     => 'スキルを編集',
		),
		'public'             => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'exclude_from_search'=> true,
		'publicly_queryable' => false,
		'has_archive'        => false,
		'menu_icon'          => 'dashicons-awards',
		'menu_position'      => 6,
		'supports'           => array( 'title', 'page-attributes' ),
		'show_in_rest'       => true,
	) );

	register_post_type( 'career', array(
		'labels' => array(
			'name'          => '経歴',
			'singular_name' => '経歴',
			'add_new_item'  => '経歴を追加',
			'edit_item'     => '経歴を編集',
		),
		'public'             => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'exclude_from_search'=> true,
		'publicly_queryable' => false,
		'has_archive'        => false,
		'menu_icon'          => 'dashicons-clock',
		'menu_position'      => 7,
		'supports'           => array( 'title', 'page-attributes' ),
		'show_in_rest'       => true,
	) );
} );

/* ---------- メタボックス共通処理 ---------- */

/**
 * フィールド定義から add_meta_box 用のレンダリング関数を生成する
 */
function takumi_meta_box_renderer( $fields, $prefix ) {
	return function ( $post ) use ( $fields, $prefix ) {
		wp_nonce_field( "takumi_{$prefix}_meta", "takumi_{$prefix}_meta_nonce" );
		foreach ( $fields as $key => $conf ) {
			$value = get_post_meta( $post->ID, '_takumi_' . $key, true );
			$type  = isset( $conf[2] ) ? $conf[2] : 'text';
			printf(
				'<p><label for="takumi_%1$s"><strong>%2$s</strong><br><small>%3$s</small></label><br>',
				esc_attr( $key ),
				esc_html( $conf[0] ),
				esc_html( $conf[1] )
			);
			if ( 'textarea' === $type ) {
				printf( '<textarea id="takumi_%1$s" name="takumi_%1$s" rows="4" style="width:100%%">%2$s</textarea>', esc_attr( $key ), esc_textarea( $value ) );
			} else {
				printf( '<input type="text" id="takumi_%1$s" name="takumi_%1$s" value="%2$s" style="width:100%%">', esc_attr( $key ), esc_attr( $value ) );
			}
			echo '</p>';
		}
	};
}

/**
 * メタボックスの入力値を保存する
 */
function takumi_save_meta_fields( $post_id, $fields, $prefix ) {
	if ( ! isset( $_POST[ "takumi_{$prefix}_meta_nonce" ] ) ||
		! wp_verify_nonce( $_POST[ "takumi_{$prefix}_meta_nonce" ], "takumi_{$prefix}_meta" ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( $fields as $key => $conf ) {
		if ( isset( $_POST[ 'takumi_' . $key ] ) ) {
			$type  = isset( $conf[2] ) ? $conf[2] : 'text';
			$value = wp_unslash( $_POST[ 'takumi_' . $key ] );
			update_post_meta( $post_id, '_takumi_' . $key, 'textarea' === $type ? sanitize_textarea_field( $value ) : sanitize_text_field( $value ) );
		}
	}
}

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

/* ---------- スキルメタボックス ---------- */
const TAKUMI_SKILL_FIELDS = array(
	'icon'       => array( 'アイコン(skillicons.dev のID)', '例: html' ),
	'experience' => array( '経験', '例: 4 yrs / Learning' ),
	'percent'    => array( '習熟度(0-100)', '例: 90' ),
	'note'       => array( '補足', '例: Webサイト制作で使用' ),
);

/* ---------- 経歴メタボックス ---------- */
const TAKUMI_CAREER_FIELDS = array(
	'date'        => array( '日付', '例: 2021.04' ),
	'description' => array( '説明', '例: 高校在学中に自身のブログサイトの制作を経験。', 'textarea' ),
);

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'takumi_work_meta', '実績情報', takumi_meta_box_renderer( TAKUMI_WORK_FIELDS, 'work' ), 'works', 'normal', 'high' );
	add_meta_box( 'takumi_skill_meta', 'スキル情報', takumi_meta_box_renderer( TAKUMI_SKILL_FIELDS, 'skill' ), 'skill', 'normal', 'high' );
	add_meta_box( 'takumi_career_meta', '経歴情報', takumi_meta_box_renderer( TAKUMI_CAREER_FIELDS, 'career' ), 'career', 'normal', 'high' );
} );

add_action( 'save_post_works', function ( $post_id ) {
	takumi_save_meta_fields( $post_id, TAKUMI_WORK_FIELDS, 'work' );
} );
add_action( 'save_post_skill', function ( $post_id ) {
	takumi_save_meta_fields( $post_id, TAKUMI_SKILL_FIELDS, 'skill' );
} );
add_action( 'save_post_career', function ( $post_id ) {
	takumi_save_meta_fields( $post_id, TAKUMI_CAREER_FIELDS, 'career' );
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
						<img src="<?php echo esc_url( takumi_skill_icon_url( $icon ) ); ?>" alt="<?php echo esc_attr( $icon ); ?>" loading="lazy">
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

/* ---------- スキル・経歴の取得 ---------- */

/**
 * スキル一覧を取得(管理画面「スキル」に投稿がなければ既定値を返す)
 * 各要素: array( アイコンID, 名前, 経験, 習熟度%, 補足 )
 */
function takumi_get_skills_data() {
	$posts = get_posts( array(
		'post_type'      => 'skill',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	) );

	if ( ! $posts ) {
		return array(
			array( 'html', 'HTML', '4 yrs', 90, 'Webサイト制作で使用' ),
			array( 'css', 'CSS', '4 yrs', 80, 'Webサイト制作で使用' ),
			array( 'js', 'JavaScript', 'Learning', 40, '経験半年・Webサイト制作で使用' ),
			array( 'php', 'PHP', 'Learning', 20, '経験半年・基礎から学習中' ),
			array( 'python', 'Python', '1 yr', 80, '基本構文を習得済み' ),
			array( 'java', 'Java', 'Learning', 10, '経験半年・基礎から学習中' ),
			array( 'django', 'Django', 'Learning', 20, '産学連携プロジェクトで制作経験あり' ),
			array( 'laravel', 'Laravel', 'Learning', 35, 'Webアプリの制作経験あり' ),
			array( 'mysql', 'MySQL', '<1 yr', 30, 'データベース構築で使用' ),
			array( 'git', 'Git', '<1 yr', 30, 'リポジトリの管理で使用' ),
			array( 'github', 'GitHub', '1 yr', 50, 'チーム開発でのリポジトリ共有で使用' ),
			array( 'docker', 'Docker', '<1 yr', 25, '開発環境の構築経験あり' ),
			array( 'wordpress', 'WordPress', '4 yrs', 90, 'Webサイト制作・テーマ開発で使用' ),
			array( 'threejs', 'Three.js', 'Learning', 20, '本サイトの3D演出で使用' ),
		);
	}

	return array_map( function ( $post ) {
		return array(
			get_post_meta( $post->ID, '_takumi_icon', true ),
			get_the_title( $post ),
			get_post_meta( $post->ID, '_takumi_experience', true ),
			(int) get_post_meta( $post->ID, '_takumi_percent', true ),
			get_post_meta( $post->ID, '_takumi_note', true ),
		);
	}, $posts );
}

/**
 * 経歴一覧を取得(管理画面「経歴」に投稿がなければ既定値を返す)
 * 各要素: array( 日付, タイトル, 説明 )
 */
function takumi_get_career_data() {
	$posts = get_posts( array(
		'post_type'      => 'career',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	) );

	if ( ! $posts ) {
		return array(
			array( '2021.04', '通信制高校に編入', '高校在学中に自身のブログサイトの制作を経験。' ),
			array( '2024.04', 'KADOKAWAドワンゴ情報工科学院 入学', '入学後、プログラミングの学習を本格的に開始。' ),
			array( '2024.09', '初めてのチーム制作を経験', '文化祭でのチーム制作を通じて協調性や責任感を養い、その経験が今の自信やキャリア形成につながっている。' ),
			array( '2024.11', '初の産学連携にチームリーダーとして参加', '産学連携プロジェクトを通じて、企業との協働方法を学ぶ。' ),
			array( '2025.04', '企業様の実案件コンペで受賞', 'コーダーとしてWebサイト制作に参加。' ),
			array( '2025.05', 'Web制作案件の営業を開始', 'クライアントとのコミュニケーションを学びながら案件の営業を開始。' ),
			
		);
	}

	return array_map( function ( $post ) {
		return array(
			get_post_meta( $post->ID, '_takumi_date', true ),
			get_the_title( $post ),
			get_post_meta( $post->ID, '_takumi_description', true ),
		);
	}, $posts );
}

/**
 * スキルアイコンのURLを取得。ローカルに /assets/img/skills/{icon}.svg があればそれを使い、
 * 無ければ skillicons.dev にフォールバックする(外部リクエスト削減のため)
 */
function takumi_skill_icon_url( $icon ) {
	$local_path = get_template_directory() . '/assets/img/skills/' . $icon . '.svg';
	if ( file_exists( $local_path ) ) {
		return get_template_directory_uri() . '/assets/img/skills/' . $icon . '.svg';
	}
	return 'https://skillicons.dev/icons?i=' . rawurlencode( $icon );
}

/**
 * トップページ Skill セクション用の上位アイコンを取得
 */
function takumi_get_top_skill_icons( $limit = 6 ) {
	$icons = array_filter( array_column( takumi_get_skills_data(), 0 ) );
	return array_slice( $icons, 0, $limit );
}

/**
 * 登山モードの吹き出し表示用に、上位スキルのアイコンID・名前を取得
 * ※ アイコン画像がローカルに無い場合(/assets/img/skills/{icon}.svg が404)でも
 *   three-climb.js 側で名前のみの吹き出しにフォールバックする
 */
function takumi_get_top_skills( $limit = 6 ) {
	$skills = array_slice( takumi_get_skills_data(), 0, $limit );

	return array_map( function ( $skill ) {
		return array(
			'icon' => $skill[0],
			'name' => $skill[1],
		);
	}, $skills );
}
