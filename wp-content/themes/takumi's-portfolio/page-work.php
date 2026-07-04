<?php
/**
 * Template Name: Work(制作実績)
 * 固定ページ「work」用テンプレート
 */
get_header();

$works = takumi_get_works();
?>

<main>
	<section class="page-hero">
		<canvas class="stars-canvas"></canvas>
		<h1 class="page-hero__title">Work</h1>
		<p class="page-hero__sub">制作実績</p>
	</section>

	<section class="section" style="padding-top: 40px;">
		<div class="container">
			<p style="color: var(--text-dim); font-size: 15.5px; margin-bottom: 48px;" data-reveal>
				これまでに手掛けた制作物をまとめています。気になる作品はクリックすると詳細をご覧いただけます。
			</p>

			<!-- Filters -->
			<div class="filter-groups" data-reveal>
				<div class="filter-row">
					<span class="filter-row__label">Type</span>
					<button class="filter-btn" data-group="type" data-value="front">フロントエンド</button>
					<button class="filter-btn" data-group="type" data-value="back">バックエンド</button>
					<button class="filter-btn" data-group="type" data-value="design">デザイン</button>
				</div>
				<div class="filter-row">
					<span class="filter-row__label">Tech</span>
					<button class="filter-btn" data-group="tech" data-value="html/css">HTML/CSS</button>
					<button class="filter-btn" data-group="tech" data-value="js">JavaScript</button>
					<button class="filter-btn" data-group="tech" data-value="php">PHP</button>
					<button class="filter-btn" data-group="tech" data-value="python">Python</button>
				</div>
				<div class="filter-row">
					<span class="filter-row__label">Year</span>
					<button class="filter-btn" data-group="year" data-value="2025">2025</button>
					<button class="filter-btn" data-group="year" data-value="2024">2024</button>
					<button class="filter-reset">すべて表示</button>
				</div>
			</div>

			<?php if ( $works ) : ?>
				<!-- 制作実績(カスタム投稿)から描画 -->
				<div class="works-grid" id="works-grid" data-source="server">
					<?php foreach ( $works as $work ) {
						takumi_render_work_card( $work );
					} ?>
				</div>
			<?php else : ?>
				<!-- 投稿が未登録の場合は works.js の同梱データで描画 -->
				<div class="works-grid" id="works-grid"></div>
			<?php endif; ?>
			<p class="works-empty">条件に一致する作品が見つかりませんでした。</p>
		</div>
	</section>
</main>

<?php get_footer(); ?>
