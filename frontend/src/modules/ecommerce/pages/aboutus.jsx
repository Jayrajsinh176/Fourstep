import { Link } from "react-router-dom";

const promises = [
  "Providing best quality product services to all of our customers",
  "Working in our IBO to provide better service and company growth",
  "Caring for the environment according to the latest industry standards",
  "Safety as top priority in assuring safe work procedures",
  "Working with technology to provide fast, accurate, and cost-effective service",
  "Living up to highest industry standards",
];

const coreValues = [
  { img: "/images/communication.png", title: "Effective Communication", desc: "A successful affiliate program relies on strong interpersonal skills to connect with potential customers and recruits. Building rapport and understanding the needs of your audience can greatly improve sales." },
  { img: "/images/training.png", title: "Training and Support", desc: "Providing your distributors with extensive training and ongoing support can help them succeed. This can include product knowledge, sales techniques, and guidance on building their own downline." },
  { img: "/images/compensation.png", title: "Compensation Plan", desc: "A well-designed compensation plan can motivate distributors and customers by offering various incentives like commissions, bonuses, and rewards for achieving specific sales targets." },
  { img: "/images/quality.png", title: "Product Quality", desc: "Providing high-quality products or services is essential to build trust and retain customers. If the products are truly valuable, it becomes easier for distributors and consumers to promote them." },
  { img: "/images/personal.png", title: "Personal Branding", desc: "Encouraging distributors and customers to build their personal brand can help them stand out in a competitive market." },
  { img: "/images/online.png", title: "Online Presence", desc: "Leveraging online platforms and social media can help reach a wider audience and connect with potential clients and recruiters." },
  { img: "/images/improvement.png", title: "Continuous Improvement", desc: "Long-term success requires adapting to market trends, seeking feedback, and continuously improving products and marketing strategies." },
  { img: "/images/leadership.png", title: "Leadership Development", desc: "Recognizing and cultivating leadership qualities in distributors and customers can lead to the growth of stronger teams and more sustainable networks." },
];
    
const stats = [
  { icon: "👥", number: "50,000+", label: "Active Distributors" },
  { icon: "📦", number: "8",       label: "Product Categories" },
  { icon: "❤️", number: "98%",     label: "Customer Satisfaction" },
  { icon: "🎓", number: "100+",    label: "Training Programs" },
];

const commitments = [
  "To Provide the Best Platform for Our People",
  "To Deliver World-Class, 100% Satisfaction-Guaranteed Products",
  "To Set New Benchmarks in Personal Care, Beauty Care, Home Care, and Health Care",
  "To Create a Stable, Growth-Focused, and Supportive Environment",
  "To Build a Culture Where Learning, Leadership, and Life Are Celebrated",
  "To Empower Every Individual with Tools for Financial Independence",
  "To Promote Ethical Business Practices with Transparency and Integrity",
  "To Inspire a Network of Passionate and Purpose-Driven Entrepreneurs",
];

const benefits = [
  "Active Income Opportunities",
  "Flexible, Open Choice Plans",
  "Attractive Consistency Rewards",
  "Family Saver Benefits",
  "Daily & Monthly Income Options",
  "Leadership Building Bonuses",
  "Exclusive, Quality-Assured Products",
  "Happy Customers, Happy Distributors",
  "Future to Success (F2S) Program",
  "Quick Registration",
  "No Overheads",
  "A Proven, Powerful Support System",
  "Training Programs to Boost Personal Development",
  "Global Business Expansion Possibilities",
  "Transparent and Ethical Business Practices",
];

function SectionHeading({ pre, highlight, post, subtitle }) {
  return (
    <div className="text-center mb-12">
      <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
        {pre && <span>{pre} </span>}
        <span className="text-red-500">{highlight}</span>
        {post && <span> {post}</span>}
      </h2>
      {subtitle && (
        <p className="text-gray-500 mt-3 max-w-xl mx-auto text-sm md:text-base">{subtitle}</p>
      )}
      <div className="w-14 h-1 bg-red-500 mx-auto mt-4 rounded-full" />
    </div>
  );
}

function CheckItem({ text, light }) {
  return (
    <li className="flex items-start gap-3 text-sm leading-relaxed">
      <span className="text-red-500 text-base mt-0.5 shrink-0">✔</span>
      <span className={light ? "text-gray-400" : "text-gray-600"}>{text}</span>
    </li>
  );
}

function AboutUs() {
  return (
    <div className="overflow-x-hidden">

      {/* ── HERO ── */}
      <section className="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white py-20 md:py-28 px-6 text-center relative overflow-hidden">
        <div className="absolute inset-0 opacity-10"
          style={{ backgroundImage: "radial-gradient(circle at 20% 50%, #ef4444 0%, transparent 50%), radial-gradient(circle at 80% 20%, #3b82f6 0%, transparent 50%)" }}
        />
        <div className="relative z-10 max-w-3xl mx-auto">
          <p className="text-red-400 uppercase tracking-widest text-xs md:text-sm font-semibold mb-4">
            Who We Are
          </p>
          <h1 className="text-4xl md:text-6xl font-extrabold mb-5 leading-tight">
            About <span className="text-red-500">Fourstep</span>
          </h1>
          <p className="text-gray-300 text-base md:text-lg leading-relaxed">
            More than a company — a community built on opportunity, trust, and achievement.
            We empower individuals to grow, lead, and succeed.
          </p>
        </div>
      </section>

      {/* ── WHY FOURSTEP ── */}
      <section className="py-16 md:py-24">
        <div className="max-w-screen-xl mx-auto px-4 md:px-6">
          <div className="flex flex-col md:flex-row items-center gap-10 md:gap-16">
            <div className="w-full md:w-1/2">
              <img
                src="/images/whyus.png"
                alt="Why Fourstep"
                className="w-full rounded-2xl shadow-xl object-cover"
              />
            </div>
            <div className="w-full md:w-1/2">
              <p className="text-red-500 uppercase tracking-widest text-xs font-semibold mb-2">
                Our Foundation
              </p>
              <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Why <span className="text-red-500">Fourstep?</span>
              </h2>
              <div className="w-14 h-1 bg-red-500 rounded-full mb-6" />
              <p className="text-gray-600 mb-4 leading-relaxed text-sm md:text-base">
                At Fourstep Retail Ltd., we believe success isn't just a goal — it's a journey we embark
                on together. More than a company, Fourstep Retail Ltd. is a community built on{" "}
                <strong className="text-gray-800">opportunity, trust, and achievement</strong>.
              </p>
              <p className="text-gray-600 leading-relaxed text-sm md:text-base">
                We are committed to empowering individuals by providing innovative business models,
                top-quality products, and a support system that encourages continuous growth. Our mission
                is to create not just financial independence, but{" "}
                <strong className="text-gray-800">holistic development</strong> — where personal
                aspirations are matched with real opportunities.
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* ── STATS ── */}
      <section className="bg-gray-900 py-14 md:py-20">
        <div className="max-w-screen-xl mx-auto px-4 md:px-6">
          <div className="text-center mb-10">
            <h2 className="text-2xl md:text-3xl font-bold text-white">
              Our <span className="text-red-500">Success</span> in Numbers
            </h2>
            <p className="text-gray-400 mt-2 text-sm md:text-base">
              Join thousands of successful entrepreneurs in our growing community
            </p>
          </div>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            {stats.map((s) => (
              <div
                key={s.label}
                className="bg-gray-800 rounded-2xl p-5 md:p-7 text-center border border-gray-700 hover:border-red-500 transition-colors"
              >
                <div className="text-3xl md:text-4xl mb-3">{s.icon}</div>
                <div className="text-2xl md:text-3xl font-extrabold text-red-400 mb-1">{s.number}</div>
                <div className="text-xs md:text-sm text-gray-400">{s.label}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── COMPANY PROFILE ── */}
      <section className="py-16 md:py-24">
        <div className="max-w-screen-xl mx-auto px-4 md:px-6">
          <SectionHeading highlight="Company" post="Profile" />
          <div className="flex flex-col md:flex-row items-start gap-10 md:gap-14">
            <div className="w-full md:w-1/2 space-y-4">
              <p className="text-gray-600 leading-relaxed text-sm md:text-base">
                Fourstep Retail Ltd. never compromises when it comes to its people. Built on strong values
                and a commitment to quality, Fourstep ensures excellence in both product standards and the
                well-being of its community. The Head Office is located in{" "}
                <strong className="text-gray-800">Vadodara, Gujarat</strong>.
              </p>
              <p className="text-gray-600 leading-relaxed text-sm md:text-base">
                Within a short span, Fourstep Retail Ltd. has expanded rapidly by establishing regional
                offices in Ahmedabad, Delhi, Patna, Kolkata, and Bengaluru — with many more locations
                planned.
              </p>
               <p className="text-gray-600 leading-relaxed text-sm md:text-base text-justify">
                Fourstep has developed a powerful earning plan designed for growth-minded individuals.
                Through integrity, consistent effort, and a positive mindset, anyone can build a strong
                income — enabling daily earnings, long-term financial security, and the opportunity to
                build your own business empire.
              </p>
            </div>
            <div className="w-full md:w-1/2">
              <img
                src="/images/compnayprofile.png"
                alt="Company Profile"
                className="w-full rounded-2xl shadow-lg mb-5 object-cover"
              />
            </div>
          </div>
        </div>
      </section>

      {/* ── MISSION & PROMISE ── */}
      <section className="py-16 md:py-24 bg-gray-50">
        <div className="max-w-screen-xl mx-auto px-4 md:px-6">
          <div className="flex flex-col md:flex-row gap-10 md:gap-16">

            {/* Mission */}
            <div className="w-full md:w-1/2 bg-white rounded-2xl p-7 md:p-10 shadow-sm border border-gray-100">
              <p className="text-red-500 uppercase tracking-widest text-xs font-semibold mb-2">
                What Drives Us
              </p>
              <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Our <span className="text-red-500">Mission</span>
              </h2>
              <div className="w-14 h-1 bg-red-500 rounded-full mb-6" />
              <p className="text-gray-600 leading-relaxed text-sm md:text-base">
                We are dedicated to improving the lives of our people at every step. Every individual is
                important, and we work hard to{" "}
                <strong className="text-gray-800">empower dreams</strong>, offer valuable education, and
                open doors to a brighter, stress-free future.
              </p>
            </div>

            {/* Promise */}
            <div className="w-full md:w-1/2 bg-white rounded-2xl p-7 md:p-10 shadow-sm border border-gray-100">
              <p className="text-red-500 uppercase tracking-widest text-xs font-semibold mb-2">
                Our Pledge
              </p>
              <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Our <span className="text-red-500">Promise</span>
              </h2>
              <div className="w-14 h-1 bg-red-500 rounded-full mb-6" />
              <ul className="flex flex-col gap-3">
                {promises.map((item) => (
                  <CheckItem key={item} text={item} />
                ))}
              </ul>
            </div>

          </div>
        </div>
      </section>

      {/* ── OUR PURPOSE ── */}
      <section className="py-16 md:py-24">
        <div className="max-w-screen-xl mx-auto px-4 md:px-6">
          <div className="flex flex-col md:flex-row items-start gap-10 md:gap-16">

            <div className="w-full md:w-7/12">
              <p className="text-red-500 uppercase tracking-widest text-xs font-semibold mb-2">
                Why We Exist
              </p>
              <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Our <span className="text-red-500">Purpose</span>
              </h2>
              <div className="w-14 h-1 bg-red-500 rounded-full mb-6" />
              <p className="text-gray-600 mb-4 leading-relaxed text-sm md:text-base">
                We're here to empower individuals and families with opportunities that lead to personal,
                professional, and financial growth. At Fourstep, you don't just work — you{" "}
                <strong className="text-gray-800">grow, lead, celebrate, and succeed</strong>.
              </p>
              <p className="text-gray-600 mb-7 leading-relaxed text-sm md:text-base">
                We go beyond traditional opportunities by fostering a supportive environment that
                encourages lifelong learning, entrepreneurial thinking, and leadership development.
              </p>
              <h4 className="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                <span>⭐</span> What Makes Us Unique
              </h4>
              <ul className="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                {benefits.map((item) => (
                  <CheckItem key={item} text={item} />
                ))}
              </ul>
            </div>

            <div className="w-full md:w-5/12">
              <img
                src="/images/purpose.png"
                alt="Our Purpose"
                className="w-full rounded-2xl shadow-lg object-cover sticky top-24"
              />
            </div>

          </div>
        </div>
      </section>

      {/* ── COMMITMENTS ── */}
      <section className="py-16 md:py-24 bg-gray-50">
        <div className="max-w-screen-xl mx-auto px-4 md:px-6">
          <div className="flex flex-col md:flex-row items-center gap-10 md:gap-16">

            <div className="w-full md:w-1/2">
              <img
                src="/images/commitments.png"
                alt="Our Commitments"
                className="w-full rounded-2xl shadow-lg object-cover"
              />
            </div>

            <div className="w-full md:w-1/2">
              <p className="text-red-500 uppercase tracking-widest text-xs font-semibold mb-2">
                Our Pledge
              </p>
              <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Our <span className="text-red-500">Commitments</span>
              </h2>
              <div className="w-14 h-1 bg-red-500 rounded-full mb-4" />
              <p className="text-gray-400 italic mb-6 text-sm">
                Driven by purpose, guided by values — we empower people to rise, lead, and transform
                their lives.
              </p>
              <ul className="flex flex-col gap-3">
                {commitments.map((item) => (
                  <CheckItem key={item} text={item} />
                ))}
              </ul>
            </div>

          </div>
        </div>
      </section>

      {/* ── CORE VALUES ── */}
      <section className="py-16 md:py-24">
        <div className="max-w-screen-xl mx-auto px-4 md:px-6">
          <SectionHeading
            highlight="Core"
            post="Values"
            subtitle="The things that make us different"
          />
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 md:gap-6">
            {coreValues.map((v) => (
              <div
                key={v.title}
                className="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-200 text-center group"
              >
                <div className="w-14 h-14 mx-auto mb-4 flex items-center justify-center rounded-xl bg-red-50 group-hover:bg-red-100 transition-colors">
                  <img src={v.img} alt={v.title} className="w-9 h-9 object-contain" />
                </div>
                <h4 className="text-sm font-bold text-gray-900 mb-2">{v.title}</h4>
                <p className="text-gray-500 text-xs leading-relaxed">{v.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── CTA ── */}
      <section className="py-16 md:py-24 bg-gray-900 relative overflow-hidden">
        <div
          className="absolute inset-0 opacity-10"
          style={{ backgroundImage: "radial-gradient(circle at 70% 50%, #ef4444 0%, transparent 60%)" }}
        />
        <div className="relative z-10 max-w-3xl mx-auto px-4 md:px-6 text-center text-white">
          <p className="text-red-400 uppercase tracking-widest text-xs font-semibold mb-4">
            Your Next Step
          </p>
          <h2 className="text-3xl md:text-4xl font-bold mb-5">
            Your Future Starts <span className="text-red-500">Here</span>
          </h2>
          <p className="text-gray-300 mb-3 leading-relaxed text-sm md:text-base">
            Fourstep is more than a business — it's your partner in success. Whether you're looking for
            financial freedom, personal growth, or a community that celebrates your achievements, you'll
            find it here.
          </p>
          <p className="text-gray-400 mb-10 leading-relaxed text-sm md:text-base">
            This is your opportunity to grow, thrive, and lead a life of significance. Let's build your
            legacy — together.
          </p>
          <a
            href="/signup"
            className="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 active:scale-95 text-white font-semibold px-10 py-3.5 rounded-full transition-all duration-200 text-sm md:text-base shadow-lg shadow-red-500/30"
          >
            Join Us Now →
          </a>
        </div>
      </section>

    </div>
  );
}

export default AboutUs;