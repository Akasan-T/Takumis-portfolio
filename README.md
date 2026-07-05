# Takumi's Portfolio

赤堀匠海(Akahori Takumi)のポートフォリオサイトです。WordPressのカスタムテーマとして構築されており、Three.jsによる3Dスクロール演出とGSAPのアニメーションを組み合わせています。

- 本番URL: https://takumisportfolio.main.jp
- ローカル開発環境: [Local by Flywheel](https://localwp.com/)

## 特徴

- **登山モード**(フロントページ `/`): スクロールに応じてThree.jsの3D富士山を登っていく体験型トップページ。麓(Profile)から山頂(Contact)まで進むと夜が明ける演出付き。
- **スキル吹き出し**: 登山シーン中、スキルステーション付近にアイコン入りの吹き出しがスクロール連動でフェード表示される。
- **和モダン × Tech Dark Theme**: ダークトーンに金・アイスブルーのアクセントを効かせた配色([style.css](wp-content/themes/takumi's-portfolio/assets/css/style.css)の`:root`変数で管理)。
- **お問い合わせフォーム**: Contact Form 7を利用。

## ディレクトリ構成

```
wp-content/themes/takumi's-portfolio/
├── functions.php          # テーマ機能・カスタム投稿タイプ・カスタマイザー設定
├── header.php / footer.php
├── front-page.php         # 登山モード(トップページ)
├── page-about.php         # Aboutページ(プロフィール・スキル・経歴)
├── page-work.php          # Workページ(制作実績一覧・フィルタ)
├── page-climb.php         # 登山モードの単体ページ版(非公開)
├── assets/
│   ├── css/style.css
│   ├── js/
│   │   ├── three-climb.js   # 登山モードの3D演出
│   │   ├── three-stars.js   # 星空パーティクル背景
│   │   ├── works.js         # 作品データ・フィルタ・カード描画
│   │   └── main.js
│   └── img/
│       └── skills/          # スキルアイコンSVG(skillicons.devからのローカルキャッシュ)
```

## コンテンツの管理

以下はすべてwp-adminの投稿画面から編集できます(コード修正不要)。

| 投稿タイプ | 管理画面 | 用途 |
| --- | --- | --- |
| スキル (`skill`) | 「スキル」 | Aboutページのスキル一覧・登山モードの吹き出し |
| 経歴 (`career`) | 「経歴」 | Aboutページの経歴タイムライン |
| 制作実績 (`works`) | 「制作実績」 | Workページ・トップページのWorkセクション |

プロフィール文言・SNSリンク・SEO/OGP設定などは「外観 → カスタマイズ」から変更できます。

### スキルアイコンについて

アイコンは [skillicons.dev](https://skillicons.dev) のIDを使用します(例: `html`, `python`, `ts`, `cs`など、すべて小文字)。`assets/img/skills/`にローカルSVGがあればそちらを優先して読み込み、外部リクエストを削減しています(`takumi_skill_icon_url()`関数)。ローカルにない場合は自動的に`skillicons.dev`から読み込みます。

## 開発メモ

- CSS/JSを変更した場合は `functions.php` の `TAKUMI_VERSION` を上げてブラウザキャッシュを更新してください。
- Three.js / GSAP はCDN経由で読み込んでいます。
- `takumi_face` や `takumi_og_image` など画像系のカスタマイザー設定は、デフォルト値に`get_template_directory_uri()`をそのまま`get_theme_mod()`の第2引数へ渡さないこと。テーマフォルダ名の`'`がURLエンコードされて`%27s`となり、WordPressコアの`get_theme_mod()`がこれをsprintfの書式指定子と誤認識して値が壊れる不具合があるため、値が空の場合はPHP側でフォールバックする実装にしています。
