
// -----main-----

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
        duration: 1.5,
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
        duration: 2,
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
        duration: 2.5,
        opacity: 0,
        y: 30,
        ease: "power2.out",
        delay: 0.4 // さらに遅れて
    });

    gsap.from(".face",{ //自分の顔写真
        scrollTrigger: {
        trigger: ".face", 
        },
        duration: 3,
        opacity: 0,
        y: 100,
        ease: "power2.out",
        delay:1.
    });
    
    gsap.from("#skill_icon_top", { //ロゴの表示
        duration: 2,     // アニメーションの長さ（秒）
        opacity: 0,        // 最初は透明
        y: 50,             // 50px下から
        ease: "power2.out" // イージング
    });

    gsap.from(".contact_top_text", { //名前の登場
        scrollTrigger: {
            trigger: ".contact_top_text",
            start: "top 80%", // 画面の下の方に来たら表示
        },
        duration: 2,
        opacity: 0,
        y: 30,
        ease: "power2.out"
    });

    gsap.from(".Work_scroll", { //名前の登場
        scrollTrigger: {
            trigger: ".Work_scroll",
            start: "top 80%", // 画面の下の方に来たら表示
        },
        duration: 3,
        opacity: 0,
        y: 30,
        ease: "power2.out"
    });
});

gsap.registerPlugin(ScrollTrigger);

    gsap.to(".main_visual", { //メインビジュアル消す
        scale: 0.5,           // 小さくなる
        opacity: 0,           // 消える
        scrollTrigger: {
            trigger: ".main_visual",  // 対象の画像
            start: "top top",         // 画像が画面の一番上に来たとき
            end: "bottom top",        // 画像が画面外に出るまで
            scrub: true,              // スクロール連動で滑らかに変化
        }
    });
;

gsap.registerPlugin(ScrollTrigger);

const profile = document.querySelector('.top-page_profile');
const skill = document.querySelector('.top-page_skill');

// 1. ピン止めだけの ScrollTrigger（1600px）
ScrollTrigger.create({
    trigger: ".top-page_background",
    start: "top top",
    end: "+=1600",
    pin: true,
    scrub: true
});

// 2. 表示切り替えの ScrollTrigger（800pxで切り替え）
ScrollTrigger.create({
    trigger: ".top-page_background",
    start: "top top",
    end: "+=800",
    onEnter: () => {
        profile.classList.add("active");
        skill.classList.remove("active");
    },
    onLeave: () => {
        profile.classList.remove("active");
        skill.classList.add("active");
    },
    onEnterBack: () => {
        profile.classList.add("active");
        skill.classList.remove("active");
    },
    onLeaveBack: () => {
        profile.classList.remove("active");
        skill.classList.remove("active");
    }
});

ScrollTrigger.create({
    trigger: ".top-page_background",
    start: "top top",
    end: "+=800",
    onEnter: () => {
        profile.classList.add("active");
        skill.classList.remove("active");
    },
    onLeave: () => {
        profile.classList.remove("active");
        skill.classList.add("active");

    // スキルアイコンをアニメーション表示（onLeave時に）
    gsap.to(".skill_top img, .skill_btm img", {
        opacity: 1,
        scale: 1,
        duration: 0.6,
        stagger: 0.1,
        ease: "power2.out"
    });
    },
    onEnterBack: () => {
        profile.classList.add("active");
        skill.classList.remove("active");

        // スキルアイコンを非表示に戻す
        gsap.to(".skill_top img, .skill_btm img", {
            opacity: 0,
            scale: 0.8,
            duration: 0.3
    });
    },
    onLeaveBack: () => {
        profile.classList.remove("active");
        skill.classList.remove("active");

        // 念のため非表示にしておく
        gsap.set(".skill_top img, .skill_btm img", {
            opacity: 0,
            scale: 0.8
        });
    }
});

window.addEventListener("DOMContentLoaded", () => {
        const tl = gsap.timeline({ repeat: -1, repeatDelay: 0.5 });

        document.querySelectorAll(".word").forEach((word) => {
            tl.add(createChildTimeline(word), "-=90%");
        });

        function createChildTimeline(element) {
            const elText = element.querySelector(".rect");
            const tl = gsap
                .timeline()
                .from(element, {
                    y: 16,
                    opacity: 0,
                    duration: 1.5,
                    ease: "power4.out",
                })
                .set(elText, { opacity: 0 })
                .to(
                    elText,
                    {
                        x: "105%",
                        duration: 2,
                        ease: "power4.out",
                    },
                "-=50%",
                );
        return tl;
    }
});

ScrollTrigger.create({
  trigger: ".Work_background",
  start: "top top",
  end: "+=120",
  pin: true,
  pinSpacing: false, // ← 絶対に必要
});

