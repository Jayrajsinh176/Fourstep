import React, { useState } from "react";
import { FaHeart, FaStar } from "react-icons/fa";
import { BsFillArrowRightCircleFill } from "react-icons/bs";
import HorizontalScroll from "./horizontalscroll";
import MemberModal from "../components/membermodal";


function ProductPage2() {
   
    const[showModal, setShowModal] = useState(false);
    const products = [
        {
            image: "/images/morelike1.jpg",
            brand: "WishCare",
            title: "Hair Growth Serum Concentrate",
            price: "₹679",
            oldPrice: "₹999",
            discount: "32% Off",
        },
        {
            image: "/images/morelike2.jpg",
            brand: "Minimalist",
            title: "Vitamin C Serum 10%",
            price: "₹599",
            oldPrice: "₹699",
            discount: "14% Off",
        },
        {
            image: "/images/morelike3.jpg",
            brand: "COSRX",
            title: "Snail 96 Mucin Essence",
            price: "₹1160",
            oldPrice: "₹1499",
            discount: "20% Off",
        },
        {
            image: "/images/morelike4.jpg",
            brand: "Dot & Key",
            title: "Hydrating Moisturizer",
            price: "₹495",
            oldPrice: "₹650",
            discount: "24% Off",
        },
        {
            image: "/images/4step5.jpg",
            brand: "Inde Wild",
            title: "Champi Hair Oil",
            price: "₹717",
            oldPrice: "₹845",
            discount: "15% Off",
        },
        {
            image: "/images/4step2.jpg",
            brand: "Mamaearth",
            title: "Onion Hair Oil",
            price: "₹399",
            oldPrice: "₹499",
            discount: "20% Off",
        },

    ];
    return (
        <div className="min-h-screen bg-white px-4 py-4">
            <div className="w-full mx-auto">
{/* More Like This Section */}
                <div className="section-wrapper px-6 ">
                    <h2 className=" text-xl font-semibold mb-6 mt-4">
                        More Like This
                    </h2>

                    <div className=" relative">
                        <HorizontalScroll>
                            <div className="overflow-x-hidden overflow-y-hidden scroll-smooth">
                                <div className="grid grid-flow-col auto-cols-[240px] gap-2">
                                    {products.map((item, index) => (
                                        <div
                                            key={index}
                                            className="flex flex-col group cursor-pointer"
                                        >
                                            <div className="relative rounded overflow-hidden">
                                                <img
                                                    src={item.image}
                                                    alt={item.title}
                                                    className="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105"
                                                />
                                                <FaHeart className="absolute top-3 right-1/5 text-gray-300 text-sm cursor-pointer" />
                                            </div>

                                            <div className="mt-3">
                                                <div className="flex items-center gap-2 mb-1 text-sm">
                                                    <p className="font-medium text-gray-400">
                                                        {item.brand}
                                                    </p>
                                                    <FaStar className="text-green-600 text-xs ml-3" />
                                                    <span className="font-medium text-gray-700">
                                                        4.4
                                                    </span>
                                                    <span className="text-gray-500 text-xs">
                                                        | 2.8K
                                                    </span>
                                                </div>

                                                <p className="text-sm text-gray-700 mb-2 font-semibold line-clamp-2">
                                                    {item.title}
                                                </p>

                                                <div className="flex items-center gap-2 text-sm font-semibold text-gray-800">
                                                    {item.price}
                                                    <span className="text-xs text-gray-400 line-through font-normal">
                                                        {item.oldPrice}
                                                    </span>
                                                    <span className="text-xs text-green-600 font-medium">
                                                        ({item.discount})
                                                    </span>
                                                </div>
                                            </div>

                                            <button
                                                className="mt-3 bg-black text-white text-sm py-2 rounded
                                                     opacity-0 translate-y-2
                                                     group-hover:opacity-100 group-hover:translate-y-0
                                                    transition-all duration-300"
                                                    onClick={() => setShowModal(true)}
                                            >
                                                Add to Bag
                                            </button>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </HorizontalScroll>
                    </div>

                </div>
{/* Best Paired With Section */}
                <div className="section-wrapper px-6">
                    <h2 className=" text-xl font-semibold  mb-6 mt-4">
                        Best Paired With
                    </h2>

                    <div className="relative">
                        <HorizontalScroll>
                            <div className="overflow-x-hidden overflow-y-hidden scroll-smooth" >
                                <div className="grid grid-flow-col auto-cols-[240px] gap-2">
                                    {products.map((item, index) => (
                                        <div
                                            key={index}
                                            className="flex flex-col group cursor-pointer"
                                        >
                                            <div className="relative rounded overflow-hidden">
                                                <img
                                                    src={item.image}
                                                    alt={item.title}
                                                    className="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105"
                                                />
                                                <FaHeart className="absolute top-3 right-1/5 text-gray-300 text-sm cursor-pointer" />
                                            </div>

                                            <div className="mt-3">
                                                <div className="flex items-center gap-2 mb-1 text-sm">
                                                    <p className="font-medium text-gray-400">
                                                        {item.brand}
                                                    </p>
                                                    <FaStar className="text-green-600 text-xs ml-3" />
                                                    <span className="font-medium text-gray-700">
                                                        4.4
                                                    </span>
                                                    <span className="text-gray-500 text-xs">
                                                        | 2.8K
                                                    </span>
                                                </div>

                                                <p className="text-sm text-gray-700 mb-2 font-semibold line-clamp-2">
                                                    {item.title}
                                                </p>

                                                <div className="flex items-center gap-2 text-sm font-semibold text-gray-800">
                                                    {item.price}
                                                    <span className="text-xs text-gray-400 line-through font-normal">
                                                        {item.oldPrice}
                                                    </span>
                                                    <span className="text-xs text-green-600 font-medium">
                                                        ({item.discount})
                                                    </span>
                                                </div>
                                            </div>

                                            <button
                                                className="mt-3 bg-black text-white text-sm py-2 rounded
                                                 opacity-0 translate-y-2
                                                group-hover:opacity-100 group-hover:translate-y-0
                                                transition-all duration-300"
                                                onClick={() => setShowModal(true)}
                                            >
                                                Add to Bag
                                            </button>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </HorizontalScroll>
                    </div>

                </div>
{/* Elevate With These Section */}
                <div className="section-wrapper px-6">
                    <h2 className=" text-xl font-semibold  mb-6 mt-4">
                        Elevate With These
                    </h2>

                    <div className=" relative">
                        <HorizontalScroll>
                            <div className="overflow-x-hidden overflow-y-hidden scroll-smooth">
                                <div className="grid grid-flow-col auto-cols-[240px] gap-2">
                                    {products.map((item, index) => (
                                        <div
                                            key={index}
                                            className="flex flex-col group cursor-pointer"
                                        >
                                            <div className="relative rounded overflow-hidden">
                                                <img
                                                    src={item.image}
                                                    alt={item.title}
                                                    className="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105"
                                                />
                                                <FaHeart className="absolute top-3 right-1/5 text-gray-300 text-sm cursor-pointer" />
                                            </div>

                                            <div className="mt-3">
                                                <div className="flex items-center gap-2 mb-1 text-sm">
                                                    <p className="font-medium text-gray-400">
                                                        {item.brand}
                                                    </p>
                                                    <FaStar className="text-green-600 text-xs ml-3" />
                                                    <span className="font-medium text-gray-700">
                                                        4.4
                                                    </span>
                                                    <span className="text-gray-500 text-xs">
                                                        | 2.8K
                                                    </span>
                                                </div>

                                                <p className="text-sm text-gray-700 mb-2 font-semibold line-clamp-2">
                                                    {item.title}
                                                </p>

                                                <div className="flex items-center gap-2 text-sm font-semibold text-gray-800">
                                                    {item.price}
                                                    <span className="text-xs text-gray-400 line-through font-normal">
                                                        {item.oldPrice}
                                                    </span>
                                                    <span className="text-xs text-green-600 font-medium">
                                                        ({item.discount})
                                                    </span>
                                                </div>
                                            </div>

                                            <button
                                                className="mt-3 bg-black text-white text-sm py-2 rounded
                                                opacity-0 translate-y-2
                                                group-hover:opacity-100 group-hover:translate-y-0
                                                transition-all duration-300"
                                                onClick={() => setShowModal(true)}
                                            >
                                                Add to Bag
                                            </button>
                                        </div>
                                    ))}
                                </div>
                            </div>

                        </HorizontalScroll>
                    </div>

                </div>
                <div className="border-t border-gray-400 mt-4 mb-8"></div>
{/* Related Reaads Section                 */}
                <div className="px-6">
                    <h2 className=" text-xl font-semibold  mb-2 ">
                        Related Reads
                    </h2>
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 items-center gap-4 mt-4 rounded">
                        <div className="flex items-start gap-3 mt-4">
                            <img
                                src="/images/related1.png"
                                alt="product"
                                className="w-24 h-24 sm:w-[7.5rem] sm:h-[7.5rem] rounded object-cover"
                            />
                            <div className=" flex-1">
                                <p className="font-semibold">
                                    6 MUA-backed blushes to have
                                    hand this wedding season
                                </p>
                                <p className="font-semibold text-xs text-gray-400 mt-5">
                                   2 MIN READ
                                </p>
                                <BsFillArrowRightCircleFill className=" text-lg cursor-pointer mt-3"/>
                            </div>
                        </div>
                         <div className="flex items-start gap-3 mt-4">
                            <img
                                src="/images/related2.png"
                                alt="product"
                                className="w-24 h-24 sm:w-[7.5rem] sm:h-[7.5rem] rounded object-cover"
                            />
                            <div className=" flex-1 ">
                                <p className="font-semibold">
                                    The ultimate Secret Santa gift
                                    guide for beauty lovers
                                </p>
                                <p className="font-semibold text-xs text-gray-400 mt-5">
                                   3 MIN READ
                                </p>
                                <BsFillArrowRightCircleFill className=" text-lg cursor-pointer mt-3"/>
                            </div>
                        </div> 
                        <div className="flex items-start gap-3 mt-4">
                            <img
                                src="/images/related3.png"
                                alt="product"
                                className="w-24 h-24 sm:w-[7.5rem] sm:h-[7.5rem] rounded object-cover"
                            />
                            <div className=" flex-1">
                                <p className="font-semibold">
                                   How To wear Y2K makeup<br/>
                                   in 2026
                                </p>
                                <p className="font-semibold text-xs text-gray-400 mt-5">
                                   4 MIN READ
                                </p>
                                <BsFillArrowRightCircleFill className=" text-lg cursor-pointer mt-3"/>
                            </div>
                        </div>

                    </div>
                </div>
{/* Member Modal Section     */}
                <MemberModal
                isOpen={showModal}
                onClose={() => setShowModal(false)}/>
            </div>

        </div>


    );
}

export default ProductPage2;
