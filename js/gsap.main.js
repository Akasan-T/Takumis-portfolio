// ロゴの動き
document.addEventListener("DOMContentLoaded", ()=> {
    gsap.from("#logo_top", { //ロゴの表示
        duration: 1.5,     // アニメーションの長さ（秒）
        opacity: 0,        // 最初は透明
        y: 50,             // 50px下から
        ease: "power2.out" // イージング
    });

    gsap.from(".main_visual", { //メインビジュアルの表示
        duration: 1.5,
        opacity: 0,
        scale: 0.95,
        ease: "power2.out",
        delay: 1.
    });

    gsap.from(".name", { //名前の登場
        scrollTrigger: {
            trigger: ".name",
            start: "top 80%", // 画面の下の方に来たら表示
        },
        duration: 1,
        opacity: 0,
        y: 30,
        ease: "power2.out"
    });

    // 名前（英語）
    gsap.from(".name_English", { //英語の名前の登場
        scrollTrigger: {
            trigger: ".name_English",
            start: "top 80%",
        },
        duration: 1,
        opacity: 0,
        y: 30,
        ease: "power2.out",
        delay: 0.2 // 少し遅れて
    });

    // プロフィール本文
    gsap.from(".profile_text", { //プロフィールの本文登場
        scrollTrigger: {
            trigger: ".profile_text",
            start: "top 80%",
        },
        duration: 1.2,
        opacity: 0,
        y: 30,
        ease: "power2.out",
        delay: 0.4 // さらに遅れて
    });

    gsap.from(".face",{ //自分の顔写真
        scrollTrigger: {
        trigger: ".face", 
        },
        duration: 2,
        opacity: 0,
        y: 100,
        ease: "power2.out",
        delay:1.
    });

});

gsap.registerPlugin(ScrollTrigger);

    gsap.to(".main_visual", {　//メインビジュアル消す
        scale: 0.5,           // 小さくなる
        opacity: 0,           // 消える
        scrollTrigger: {
            trigger: ".main_visual",  // 対象の画像
            start: "top top",         // 画像が画面の一番上に来たとき
            end: "bottom top",        // 画像が画面外に出るまで
            scrub: true,              // スクロール連動で滑らかに変化
        }
    });

    ScrollTrigger.create({ //プロフィールのカードを800スクロール止める
        trigger: ".top-page_background",
        start: "top top",
        end: "+=800",
        pin: true,
        scrub: true
    });

    gsap.to(".top-page_profile", { //プロフィールカードを左にスクロール
        x: "-100%", // 左に画面外へ
        scrollTrigger: {
            trigger: ".top-page_profile",
            start: "top+=800 top", // pinが終わった直後
            end: "+=800",          // スライドに必要なスクロール量
            scrub: true
        }
    });

    gsap.from(".top-page_skill", { //スキルをスクロールさせてくる
        x: "100%", // 右の外から
        scrollTrigger: {
            trigger: ".top-page_background",
            start: "top+=800 top", // 同じタイミングで
            end: "+=800",
            scrub: true
        }
    });

    gsap.to(".horizontal-container", {
        x: () => `-${window.innerWidth}px`, // ← 明示的に100vw分スライド
        scrollTrigger: {
            trigger: ".top-page_background",
            start: "top top",
            end: "+=1000", // ← このスクロール量分だけで止める
            pin: true,
            scrub: true
        }
    });