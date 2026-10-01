import React from "react";
import {
    ShoppingBag,
    Users,
    Trophy,
    Star,
    UserPlus,
    List,
    BookOpen,
    Target,
    Briefcase,
    CheckCircle,
    Info,
    ArrowRight,
    LogIn,
    Sparkles,
} from "lucide-react";

/* ─── Data ─────────────────────────────────────────────── */

const steps = [
    { num: 1, label: "Get a sponsor or mentor", icon: UserPlus },
    { num: 2, label: "Choose your package", icon: List },
    { num: 3, label: "Access training materials", icon: BookOpen },
    { num: 4, label: "Start building your future", icon: Target },
];

const earnings = [
    {
        icon: ShoppingBag,
        title: "Retail profits",
        desc: "Earn direct profits on every product sale with competitive margins designed for your success.",
    },
    {
        icon: Users,
        title: "Team commissions",
        desc: "Build your network and earn commissions from your team's performance and achievements.",
    },
    {
        icon: Trophy,
        title: "Bonuses & rewards",
        desc: "Unlock special bonuses and exclusive rewards as you reach new milestones and targets.",
    },
    {
        icon: Star,
        title: "Leadership incentives",
        desc: "Advance to leadership positions and enjoy premium incentives, recognition, and benefits.",
    },
];


/* ─── Tiny helpers ─────────────────────────────────────── */

function OrangeText({ children }) {
    return <span className="text-orange-700">{children}</span>;
}

function BlueText({ children }) {
    return <span className="text-blue-800">{children}</span>;
}

function OrangeDivider({ center = false }) {
    return (
        <div
            className={`mt-3 mb-6 h-[3px] w-12 rounded-full bg-orange-400${center ? " mx-auto" : ""
                }`}
        />
    );
}


/* ─── Hero ──────────────────────────────────────────────── */

// function Hero() {
//     return (
//         <section className="pt-10 pb-20 bg-white">
//             <div className="max-w-6xl mx-auto px-6 text-center">
//                 <h1 className="text-5xl md:text-6xl font-black text-gray-900 leading-[1.1] mb-6 max-w-3xl mx-auto">
//                     Build a business you&apos;re{" "}
//                     <span className="relative inline-block">
//                         <OrangeText>proud of</OrangeText>
//                         <span className="absolute left-0 -bottom-1 w-full h-[4px] bg-orange-200 rounded-full -z-10" />
//                     </span>
//                 </h1>
//                 <p className="text-gray-500 text-[17px] leading-relaxed max-w-xl mx-auto mb-10">
//                     Join thousands of members already earning with Fourstep&apos;s proven health &amp;
//                     beauty business model — flexible, rewarding, and 100% yours.
//                 </p>
//                 <div className="flex flex-wrap justify-center gap-3">
//                     <a
//                         href="/member/signup"
//                         className="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-900 text-white font-semibold px-7 py-3.5 rounded-xl text-[15px] transition-colors no-underline"
//                     >
//                         Start as Promoter <ArrowRight className="w-4 h-4" />
//                     </a>
//                     <a
//                         href="/"
//                         className="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold px-7 py-3.5 rounded-xl text-[15px] transition-colors no-underline"
//                     >
//                         Open a Branch <Home className="w-4 h-4" />
//                     </a>
//                 </div>
//             </div>
//         </section>
//     );
// }

/* ─── Opportunity section ───────────────────────────────── */

function OpportunitySection() {
    return (
        <section className="py-24 bg-white">
            <div className="max-w-6xl mx-auto px-6 grid md:grid-cols-2 gap-20 items-center">

                {/* Illustration card */}
                <div className="relative">
                    <img
                        src="/images/compnayprofile.png"
                        alt="Company Profile"
                        className="w-full rounded-2xl shadow-lg mb-5 object-cover"
                    />
                </div>

                {/* Copy */}
                <div>
                    <h2 className="text-4xl font-bold text-gray-900 leading-tight">
                        Are you ready to{" "}
                        <OrangeText>live your dreams?</OrangeText>
                    </h2>
                    <OrangeDivider />
                    <p className="text-gray-500 text-[15px] leading-7 mb-4">
                        Fourstep Retail Ltd. makes it possible. We offer you the chance to{" "}
                        <strong className="text-gray-800 font-semibold">Own your business</strong> and
                        secure your financial future with a powerful, proven marketing plan designed for
                        real people with real ambitions.
                    </p>
                    <p className="text-gray-500 text-[15px] leading-7 mb-4">
                        With the right tools, guidance, and community, your journey to success starts
                        here and now.
                    </p>
                    <p className="text-gray-500 text-[15px] leading-7">
                        Our exclusive health and beauty products, paired with a proven business
                        opportunity, give you everything you need for a{" "}
                        <strong className="text-gray-800 font-semibold">
                            healthier, wealthier, and more independent life
                        </strong>
                        . You&apos;ll earn simply by sharing this dream with others.
                    </p>
                </div>

            </div>
        </section>
    );
}



/* ─── Earnings section ──────────────────────────────────── */

function EarningsSection() {
    return (
        <section className="py-24 bg-gray-50">
            <div className="max-w-6xl mx-auto px-6">
                <div className="text-center mb-10">
                    <h2 className="text-4xl font-bold text-gray-900 leading-tight">
                        What do <BlueText>I earn?</BlueText>
                    </h2>
                    <div className="mt-3 mb-6 h-[3px] w-12 rounded-full bg-orange-400 mx-auto" />
                    <p className="text-gray-500 text-[15px] leading-relaxed max-w-xl mx-auto">
                        Four ways to earn unlimited income with Fourstep Retail Ltd. A powerful,
                        proven financial rewards system developed for dreamers, doers, and everyday
                        achievers.
                    </p>
                </div>

                <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    {earnings.map(({ icon: Icon, title, desc }, i) => (
                        <div
                            key={title}
                            className="bg-white border border-gray-100 rounded-2xl p-7 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200"
                        >
                            <div
                                className={`w-12 h-12 rounded-xl flex items-center justify-center mb-5 ${i % 2 === 0 ? "bg-blue-50" : "bg-orange-50"
                                    }`}
                            >
                                <Icon
                                    className={`w-5 h-5 ${i % 2 === 0 ? "text-blue-600" : "text-orange-500"
                                        }`}
                                />
                            </div>
                            <h4 className="text-[15px] font-semibold text-gray-900 mb-2">{title}</h4>
                            <p className="text-[13px] text-gray-500 leading-relaxed">{desc}</p>
                        </div>
                    ))}
                </div>


            </div>
        </section>
    );
}

/* ─── CTA / paths section ───────────────────────────────── */

function CtaSection() {
    return (
        <section className="py-24 bg-white">
            <div className="max-w-6xl mx-auto px-6">

                {/* Header */}
                <div className="text-center mb-14">
                    <h2 className="text-4xl font-bold text-gray-900 leading-tight">
                        Start Your <OrangeText>Journey</OrangeText>
                    </h2>
                    <div className="mt-3 mb-6 h-[3px] w-12 rounded-full bg-orange-400 mx-auto" />
                    <p className="text-gray-500 text-[15px] leading-relaxed max-w-xl mx-auto">
                        Join FourStep Retail Ltd. as an Independent Business Owner (IBO) and start building your future with our proven business opportunity.
                    </p>
                </div>

                {/* Cards */}
                <div className="max-w-3xl mx-auto">
                    <div className="rounded-3xl bg-gray-800 p-10">

                        <div className="w-16 h-16 rounded-2xl bg-white/15 flex items-center justify-center mb-6">
                            <Briefcase className="w-8 h-8 text-white" />
                        </div>

                        <h3 className="text-3xl font-bold text-white mb-3">
                            Independent Business Owner (IBO)
                        </h3>

                        <p className="text-white/80 text-[15px] leading-relaxed mb-8">
                            Become an Independent Business Owner (IBO) with FourStep Retail Ltd.
                            Build your own business by promoting premium health and beauty products, growing your network, and earning through
                            our proven compensation plan. Enjoy the freedom to work at your own pace while creating a sustainable source of income.
                        </p>

                        <ul className="space-y-3 mb-10">
                            <li className="flex items-center gap-3 text-white">
                                <CheckCircle className="w-5 h-5 text-green-400" />
                                Flexible working schedule
                            </li>

                            <li className="flex items-center gap-3 text-white">
                                <CheckCircle className="w-5 h-5 text-green-400" />
                                Unlimited earning potential
                            </li>

                            <li className="flex items-center gap-3 text-white">
                                <CheckCircle className="w-5 h-5 text-green-400" />
                                Professional training & support
                            </li>
                            <li className="flex items-center gap-3 text-white">
                                <CheckCircle className="w-5 h-5 text-green-400" />
                                Own your busines
                            </li>
                            <li className="flex items-center gap-3 text-white">
                                <CheckCircle className="w-5 h-5 text-green-400" />
                                Exclusive territory
                            </li>
                            <li className="flex items-center gap-3 text-white">
                                <CheckCircle className="w-5 h-5 text-green-400" />
                                Full training
                            </li>
                        </ul>

                        <div className="flex flex-wrap gap-4">
                            <a
                                href="/member/signup"
                                className="inline-flex items-center gap-2 bg-white text-gray-900 rounded-xl px-6 py-3 font-semibold hover:bg-gray-100 transition no-underline"
                            >
                                Create Account
                                <ArrowRight className="w-4 h-4" />
                            </a>

                            <a
                                href="/member/signin"
                                className="inline-flex items-center gap-2 border border-white/30 text-white rounded-xl px-6 py-3 font-medium hover:bg-white/10 transition no-underline"
                            >
                                <LogIn className="w-4 h-4" />
                                Login
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        </section>
    );
}

/* ─── Root ──────────────────────────────────────────────── */

export default function OpportunityPage() {
    return (
        <div className="overflow-x-hidden font-sans antialiased">
            <main>
                {/* <Hero /> */}
                <OpportunitySection />

                <EarningsSection />
                <CtaSection />
            </main>
        </div>
    );
}
