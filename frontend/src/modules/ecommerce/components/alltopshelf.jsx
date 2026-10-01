import React from "react";
import { GoVerified } from "react-icons/go";
import { FaShippingFast, FaHeart, FaStar } from "react-icons/fa";
import { TbTruckReturn } from "react-icons/tb";
import { GrUserExpert } from "react-icons/gr";
import MemberModal from "../components/membermodal";
import ProgressBar from "./progressbar";
function HomeTopShelf() {


    return (
        <div className="min-h-screen bg-white px-4 py-4">
            <div className="w-full mx-auto">

                {/*         TOP SHELF           */}
                <div className="px-0 sm:px-6 relative">
                    <div className="rounded-2xl overflow-hidden" style={{ boxShadow: '0 2px 16px rgba(0,0,0,0.07)' }}>
                        <img src="/images/B1.jpg" alt="Top Shelf" className="w-full h-auto object-contain block" />
                    </div>

                    {/* <div className="absolute top-1/2 left-6 -translate-y-1/2 text-gray-700 ml-10">
                        <h2 className="text-4xl mb-2 tracking-widest">
                            TOP <strong>SHELF</strong>
                        </h2>
                        <p className="text-lg">
                            Beauty news, reviews and hot takes
                        </p>
                        <button
                            onClick={handleexplore}
                            className="mt-4 px-8 py-2 bg-white text-gray-700 rounded hover:bg-gray-100 font-semibold"
                        >
                            Explore Now
                        </button>
                    </div> */}
                </div>


                {/*         CHAT SECTION         */}
                <div className="px-0 sm:px-6 relative mt-10 sm:mt-20">
                    <div className="relative  overflow-hidden">
                        <img
                            src="/images/B2.jpg"
                            alt="Chat"
                            className="w-full h-40 sm:h-50 object-contain sm:object-cover"
                        />
                    </div>

                </div>

                {/*         AUTHENTICATION SECTION         */}
                <ProgressBar />

            </div>
        </div>
    );
}

export default HomeTopShelf;
