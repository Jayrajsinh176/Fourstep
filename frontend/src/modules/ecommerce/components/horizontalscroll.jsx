import React, { useRef, useState, useEffect } from "react";

function HorizontalScroll({ children }) {
    const scrollRef = useRef(null);
    const [showLeft, setShowLeft] = useState(false);
    const [showRight, setShowRight] = useState(true);

    const scroll = (direction) => {
        const container = scrollRef.current;
        const scrollAmount = 350;

        if (!container) return;

        container.scrollBy({
            left: direction === "left" ? -scrollAmount : scrollAmount,
            behavior: "smooth",
        });
    };

    const handleScroll = () => {
        const container = scrollRef.current;
        if (!container) return;

        const isAtStart = container.scrollLeft <= 0;
        const isAtEnd =
            container.scrollLeft + container.clientWidth >=
            container.scrollWidth - 5;

        setShowLeft(!isAtStart);
        setShowRight(!isAtEnd);
    };

    useEffect(() => {
        handleScroll();
    }, []);

    return (
        <div className="relative">

            {showLeft && (
                <button
                    onClick={() => scroll("left")}
                    className="absolute -left-4 top-1/2 -translate-y-1/2 z-10 
                             bg-white shadow-md rounded-full w-13 h-13 
                            flex items-center justify-center text-3xl"
                >
                    ‹
                </button>
            )}

            <div
                ref={scrollRef}
                onScroll={handleScroll}
                className="overflow-x-hidden scroll-smooth"
            >
                <div className="flex gap-5 w-max">
                    {children}
                </div>
            </div>

            {showRight && (
                <button
                    onClick={() => scroll("right")}
                    className="absolute -right-4 top-1/2 -translate-y-1/2 z-10 
              bg-white shadow-md rounded-full w-13 h-13 
              flex items-center justify-center text-3xl"
                >
                    ›
                </button>
            )}

        </div>
    );
}

export default HorizontalScroll;
