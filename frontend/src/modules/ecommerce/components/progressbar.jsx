import React, { useEffect, useRef, useState } from "react";
import { TrendingUp, Users, Package, Award } from "lucide-react";

const stats = [
  {
    id: 1,
    value: 100,
    display: "75+",
    label: "Quality Products ",
    icon: Package,
    gradient: "from-blue-600 to-blue-700",
    bg: "bg-amber-50",
    iconColor: "text-amber-700",
  },
  {
    id: 2,
    value: 99,
    display: "99%",
    label: "Customer Satisfaction",
    icon: Award,
    gradient: "from-amber-700 to-amber-800",
    bg: "bg-amber-50",
    iconColor: "text-amber-700",
  },
  {
    id: 3,
    value: 80,
    display: "5000+",
    label: "Orders Delivered",
    icon: TrendingUp,
    gradient: "from-blue-600 to-blue-700",
    bg: "bg-amber-50",
    iconColor: "text-amber-700",
  },
  {
    id: 4,
    value: 95,
    display: "95%",
    label: "Repeat Customers",
    icon: Users,
    gradient: "from-amber-700 to-amber-800",
    bg: "bg-amber-50",
    iconColor: "text-amber-700",
  },
];

function StatCard({ stat, isVisible, idx }) {
  const [progress, setProgress] = useState(0);
  const Icon = stat.icon;

  useEffect(() => {
    if (!isVisible) {
      setProgress(0);
      return;
    }

    let start = null;
    let frame;

    const animate = (timestamp) => {
      if (!start) start = timestamp;
      const elapsed = timestamp - start;
      const duration = 2000;

      const ratio = Math.min(elapsed / duration, 1);
      const ease = 1 - Math.pow(1 - ratio, 3);

      // progress bar capped at 100, but animates up to stat.value
      setProgress(ease * Math.min(stat.value, 100));

      if (ratio < 1) {
        frame = requestAnimationFrame(animate);
      }
    };

    frame = requestAnimationFrame(animate);
    return () => frame && cancelAnimationFrame(frame);
  }, [isVisible, stat.value]);

  return (
    <div
      className={`card ${isVisible ? 'visible' : ''}`}
      style={{ transitionDelay: `${idx * 100}ms` }}
    >
      <div className="card-glow" />

      <div className="card-content">
        <div className="card-header">
          <div className={`icon-wrap ${stat.bg}`}>
            <Icon className={`icon ${stat.iconColor}`} />
          </div>
        </div>

        <div className="stats-display">
          {/* Always show exactly what's written in display */}
          <h3 className="stat-number">{stat.display}</h3>
          <p className="stat-label">{stat.label}</p>
        </div>

        <div className="progress-container">
          {/* Bar width capped at 100% regardless of value */}
          <div
            className={`progress-bar bg-gradient-to-r ${stat.gradient}`}
            style={{ width: `${progress}%` }}
          >
            <div className="progress-shine" />
          </div>
        </div>

        <div className="progress-info">
          <span>Progress</span>
          {/* Show actual value in progress info, capped display at 100% */}
          <span>{Math.min(Math.round(progress), 100)}%</span>
        </div>
      </div>
    </div>
  );
}

export default function ProgressBar() {
  const ref = useRef(null);
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    const el = ref.current;
    if (!el) return;

    const obs = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setTimeout(() => setVisible(true), 150);
        } else {
          setVisible(false);
        }
      },
      { threshold: 0.3 }
    );

    obs.observe(el);
    return () => obs.disconnect();
  }, []);

  return (
    <section ref={ref} className="stats-section">
      <div className="stats-container">
        <div className="stats-grid">
          {stats.map((stat, i) => (
            <StatCard key={stat.id} stat={stat} isVisible={visible} idx={i} />
          ))}
        </div>
      </div>

      <style jsx>{`
        .stats-section {
          background: #ffffff;
          padding: 2rem 1rem;
        }

        @media (min-width: 640px) {
          .stats-section { padding: 2.5rem 1.5rem; }
        }

        @media (min-width: 768px) {
          .stats-section { padding: 3rem 2rem; }
        }

        .stats-container {
          max-width: 1280px;
          margin: 0 auto;
        }

        .stats-grid {
          display: grid;
          grid-template-columns: 1fr;
          gap: 0.75rem;
        }

        @media (min-width: 640px) {
          .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
          }
        }

        @media (min-width: 1024px) {
          .stats-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
          }
        }

        .card {
          position: relative;
          background: #f3f4f6;
          border: 1px solid #e5e7eb;
          border-radius: 0.5rem;
          padding: 0.875rem;
          overflow: hidden;
          opacity: 0;
          transform: translateY(2rem);
          transition: all 0.6s ease;
        }

        .card.visible {
          opacity: 1;
          transform: translateY(0);
        }

        .card:hover {
          transform: translateY(-0.25rem);
          box-shadow: 0 8px 20px rgba(0,0,0,0.08);
          border-color: #d1d5db;
          background: #e5e7eb;
        }

        @media (min-width: 640px) {
          .card { padding: 1rem; }
        }

        .card-glow {
          position: absolute;
          inset: 0;
          background: linear-gradient(135deg, #ffffff 0%, transparent 100%);
          opacity: 0;
          transition: opacity 0.3s;
        }

        .card:hover .card-glow {
          opacity: 0.5;
        }

        .card-content {
          position: relative;
          z-index: 1;
        }

        .card-header {
          display: flex;
          justify-content: space-between;
          align-items: flex-start;
          margin-bottom: 0.625rem;
        }

        .icon-wrap {
          padding: 0.5rem;
          border-radius: 0.375rem;
          transition: transform 0.3s;
        }

        .card:hover .icon-wrap {
          transform: scale(1.1);
        }

        @media (min-width: 640px) {
          .icon-wrap { padding: 0.5rem; }
        }

        .icon {
          width: 1rem;
          height: 1rem;
        }

        @media (min-width: 640px) {
          .icon {
            width: 1.125rem;
            height: 1.125rem;
          }
        }

        .stats-display {
          margin-bottom: 0.5rem;
        }

        .stat-number {
          font-size: 1.25rem;
          font-weight: 700;
          color: #111827;
          line-height: 1;
          letter-spacing: -0.025em;
        }

        @media (min-width: 640px) {
          .stat-number { font-size: 1.5rem; }
        }

        @media (min-width: 768px) {
          .stat-number { font-size: 1.75rem; }
        }

        .stat-label {
          font-size: 0.6875rem;
          font-weight: 600;
          color: #374151;
          margin-top: 0.25rem;
        }

        @media (min-width: 640px) {
          .stat-label { font-size: 0.75rem; }
        }

        @media (min-width: 768px) {
          .stat-label {
            font-size: 0.8125rem;
            margin-top: 0.375rem;
          }
        }

        .progress-container {
          position: relative;
          height: 0.25rem;
          background: #d1d5db;
          border-radius: 9999px;
          overflow: hidden;
        }

        @media (min-width: 640px) {
          .progress-container { height: 0.3125rem; }
        }

        .progress-bar {
          position: absolute;
          top: 0;
          left: 0;
          height: 100%;
          border-radius: 9999px;
          transition: width 0.5s ease;
        }

        .progress-shine {
          position: absolute;
          inset: 0;
          background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
          animation: shine 2s infinite;
        }

        @keyframes shine {
          0% { transform: translateX(-100%); }
          100% { transform: translateX(100%); }
        }

        .progress-info {
          display: flex;
          justify-content: space-between;
          margin-top: 0.375rem;
          font-size: 0.625rem;
          color: #4b5563;
          font-weight: 500;
        }

        @media (min-width: 640px) {
          .progress-info { font-size: 0.6875rem; }
        }
      `}</style>
    </section>
  );
}