import React, { useState, useEffect, useRef } from "react";
import api from "../api/axios";
import { useNavigate } from "react-router-dom";

function HomeBanner() {
  const [current, setCurrent] = useState(0);
  const [categories, setCategories] = useState([]);
  const navigate = useNavigate();
  const videoRefs = useRef({});

  // ─── 8 Banners: add your actual src paths here ───────────────────────────
  const slides = [
    { type: "image", src: "/images/Artboard 1.jpg" },
    { type: "image", src: "/images/Artboard 2.jpg" },
    { type: "image", src: "/images/Artboard 3.jpg" },
    { type: "image", src: "/images/Artboard 4.jpg" },
    { type: "image", src: "/images/Artboard 5.jpg" },
    { type: "image", src: "/images/Artboard 6.jpg" },
    { type: "image", src: "/images/Artboard 7.jpg" },
    { type: "image", src: "/images/Artboard 8.jpg" },
    { type: "image", src: "/images/Artboard 9.jpg" },
    { type: "image", src: "/images/Artboard 10.jpg" },
    { type: "image", src: "/images/Artboard 11.jpg" },
    { type: "image", src: "/images/Artboard 12.jpg" },
    { type: "image", src: "/images/Artboard 13.jpg" },




    // To use a video instead, swap type to "video" and use a .mp4 src:
    // { type: "video", src: "/images/video1.mp4" },
  ];

  // Fetch categories
  useEffect(() => {
    api.get("/categories")
      .then((res) => setCategories(res.data))
      .catch((err) => console.log(err));
  }, []);

  // Auto-advance every 5 s
  useEffect(() => {
    const timer = setInterval(() => {
      setCurrent((prev) => (prev + 1) % slides.length);
    }, 5000);
    return () => clearInterval(timer);
  }, [slides.length]);

  // Play / pause videos when slide changes
  useEffect(() => {
    slides.forEach((slide, i) => {
      if (slide.type === "video" && videoRefs.current[i]) {
        if (i === current) {
          videoRefs.current[i].currentTime = 0;
          videoRefs.current[i].play().catch(() => { });
        } else {
          videoRefs.current[i].pause();
        }
      }
    });
  }, [current]);

  const prev = () => setCurrent((c) => (c - 1 + slides.length) % slides.length);
  const next = () => setCurrent((c) => (c + 1) % slides.length);

  return (
    <>
      <style>{`
        /* ── Base ─────────────────────────────────────────────── */
        .hb-wrap { font-family: 'DM Sans', sans-serif; }

        /* ── Promo bar ────────────────────────────────────────── */
        .promo-bar {
          background: #111; color: #fff;
          text-align: center; font-size: 13px; font-weight: 500;
          padding: 9px 16px; display: flex; align-items: center;
          justify-content: center; gap: 8px; letter-spacing: 0.01em;
        }
        .promo-code {
          background: #fff; color: #111; font-weight: 700;
          font-size: 12px; letter-spacing: 0.08em;
          padding: 2px 10px; border-radius: 4px;
        }

        /* ── Slider shell ─────────────────────────────────────── */
        .slider-outer {
          position: relative; width: 100%; overflow: hidden;
          background: #e8ede8;
          /* Aspect ratio that matches a standard wide banner (e.g. 16:5).
             Adjust the padding-bottom percentage to match your actual image ratio. */
          aspect-ratio: 16 / 5;
        }
        /* Fallback for older browsers that don't support aspect-ratio */
        @supports not (aspect-ratio: 16/5) {
          .slider-outer { padding-bottom: 31.25%; height: 0; }
        }

        /* ── Individual slide layers ──────────────────────────── */
        .slide-layer {
          position: absolute; inset: 0; width: 100%; height: 100%;
          opacity: 0; transition: opacity 0.55s ease;
          pointer-events: none;
        }
        .slide-layer.active {
          opacity: 1; pointer-events: auto;
        }

        /* ── Media inside slides ──────────────────────────────── */
        .slide-layer img,
        .slide-layer video {
          width: 100%; height: 100%;
          display: block;
          object-fit: cover;         /* fills the box, crops if needed */
          object-position: center;   /* keeps the focus area centred */
        }

        /* ── Arrows ───────────────────────────────────────────── */
        .slider-arrow {
          position: absolute; top: 50%; transform: translateY(-50%);
          z-index: 10; width: 42px; height: 42px; border-radius: 50%;
          background: rgba(255,255,255,0.88); border: none; cursor: pointer;
          display: flex; align-items: center; justify-content: center;
          box-shadow: 0 2px 14px rgba(0,0,0,0.14); color: #111;
          transition: background 0.2s, transform 0.2s;
        }
        .slider-arrow:hover { background: #fff; transform: translateY(-50%) scale(1.08); }
        @media (max-width: 480px) {
          .slider-arrow { width: 32px; height: 32px; }
          .slider-arrow svg { width: 14px; height: 14px; }
        }

        /* ── Dots ─────────────────────────────────────────────── */
        .slider-dots {
          position: absolute; bottom: 14px; left: 50%; transform: translateX(-50%);
          display: flex; gap: 7px; align-items: center; z-index: 10;
        }
        .sdot {
          width: 8px; height: 8px; border-radius: 50%;
          border: none; cursor: pointer; padding: 0;
          background: rgba(255,255,255,0.5); transition: all 0.3s;
        }
        .sdot.active { background: #fff; width: 22px; border-radius: 4px; }
        @media (max-width: 480px) {
          .slider-dots { bottom: 8px; gap: 5px; }
          .sdot { width: 6px; height: 6px; }
          .sdot.active { width: 16px; }
        }

        /* ── Category grid ────────────────────────────────────── */
       .cat-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 18px;
  align-items: stretch;
}
       @media (max-width: 1200px) {
  .cat-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .cat-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }
}

@media (max-width: 480px) {
  .cat-grid {
    grid-template-columns: repeat(1, 1fr);
  }
}

       .cat-card {
  position: relative;
  border-radius: 16px;
  overflow: hidden;
  cursor: pointer;
  aspect-ratio: 16 / 9;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.cat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 30px rgba(0,0,0,0.12);
}

.cat-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  display: block;
  transition: transform 0.4s ease;
}
        .cat-card:hover img { transform: scale(1.05); }
      .cat-card {
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  background: #efefef;
  cursor: pointer;
  transition: box-shadow 0.25s, transform 0.25s;
  aspect-ratio: 16 / 9;
}
  z-index: 1;
}
        .cat-content {
          position: relative; z-index: 2;
          padding: 22px 20px 20px;
          display: flex; flex-direction: column;
          height: 100%; justify-content: space-between;
        }
        .cat-name { font-size: 17px; font-weight: 700; color: #111; line-height: 1.2; margin-bottom: 5px; }
        @media (max-width: 640px) { .cat-name { font-size: 14px; } }
        .cat-count { font-size: 12px; color: #777; font-weight: 500; }
        .cat-btn {
          display: inline-flex; align-items: center; gap: 6px;
          margin-top: 18px; background: #111; color: #fff;
          font-size: 12px; font-weight: 600; padding: 8px 16px;
          border-radius: 6px; border: none; cursor: pointer;
          letter-spacing: 0.02em; width: fit-content; transition: background 0.2s;
        }
        .cat-btn:hover { background: #333; }
        @media (max-width: 640px) { .cat-btn { font-size: 11px; padding: 7px 12px; margin-top: 12px; } }
      `}</style>

      <div className="hb-wrap bg-white">

        {/* ── Promo bar ────────────────────────────────────────── */}
        {/* <div className="promo-bar">
          🎉 Enjoy up to ₹2000 off. Use code:
          <span className="promo-code">BIGSALE26</span>
        </div> */}

        {/* ── Slider ───────────────────────────────────────────── */}
        <div className="slider-outer">

          {slides.map((slide, i) => (
            <div
              key={slide.src}
              className={`slide-layer ${i === current ? "active" : ""}`}
            >
              {slide.type === "video" ? (
                <video
                  ref={(el) => { if (el) videoRefs.current[i] = el; }}
                  src={slide.src}
                  preload="metadata"
                  muted
                  loop
                  playsInline
                />
              ) : (
                <img
                  src={slide.src}
                  alt={`Banner ${i + 1}`}
                  loading={i === 0 ? "eager" : "lazy"}
                  sizes="100vw"
                  // fetchpriority on first banner helps LCP
                  {...(i === 0 ? { fetchpriority: "high" } : {})}
                />
              )}
            </div>
          ))}

          {/* Arrows */}
          <button
            className="slider-arrow"
            style={{ left: 14 }}
            onClick={prev}
            aria-label="Previous slide"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" strokeWidth="2.5"
              strokeLinecap="round" strokeLinejoin="round">
              <polyline points="15 18 9 12 15 6" />
            </svg>
          </button>
          <button
            className="slider-arrow"
            style={{ right: 14 }}
            onClick={next}
            aria-label="Next slide"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" strokeWidth="2.5"
              strokeLinecap="round" strokeLinejoin="round">
              <polyline points="9 18 15 12 9 6" />
            </svg>
          </button>

          {/* Dots */}
          <div className="slider-dots">
            {slides.map((_, i) => (
              <button
                key={i}
                className={`sdot ${i === current ? "active" : ""}`}
                onClick={() => setCurrent(i)}
                aria-label={`Go to slide ${i + 1}`}
              />
            ))}
          </div>
        </div>

        {/* ── Categories ───────────────────────────────────────── */}
        <div className="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-10 py-12 sm:py-16">
          <div className="mb-8">
            <p className="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">
              ✦ Browse by
            </p>
            <h2 className="text-2xl sm:text-3xl font-bold text-gray-900">Popular Categories</h2>
          </div>

          <div className="cat-grid">
            {categories.map((item, idx) => (
              <div
                key={item.id}
                className="cat-card"
                onClick={() => navigate(`/category/${item.id}`)}
              >
                <img
                  src={item.image}
                  alt={item.name}
                  loading={idx < 2 ? "eager" : "lazy"}
                  sizes="(max-width:540px) 50vw, (max-width:1024px) 50vw, 25vw"
                />

                <div className="cat-content">
                  {item.product_count && (
                    <p className="cat-count">
                      {item.product_count} Products
                    </p>
                  )}
                </div>
              </div>
            ))}
          </div>
        </div>

      </div>
    </>
  );
}

export default HomeBanner;