import { useRef, useState } from "react";

const LENS_SIZE = 150;
const PREVIEW_SIZE = 450;
const ZOOM = PREVIEW_SIZE / LENS_SIZE; // keeps lens contents and preview perfectly in sync

export default function ImageZoom({ image }) {
    const containerRef = useRef(null);
    const imgRef = useRef(null);
    const [showLens, setShowLens] = useState(false);
    const [lensPosition, setLensPosition] = useState({ x: 0, y: 0 });
    // crop = top-left corner (in rendered-image pixels) of the LENS_SIZE box being magnified
    const [crop, setCrop] = useState({ x: 0, y: 0 });
    // size of the image as actually rendered on screen (post object-contain)
    const [imgBox, setImgBox] = useState({ width: 0, height: 0 });

    // Returns the actual rendered image box inside the square container,
    // accounting for object-contain letterboxing (blank space top/bottom or left/right).
    const getImageRect = () => {
        const container = containerRef.current;
        const img = imgRef.current;
        if (!container || !img || !img.naturalWidth) return null;

        const containerRect = container.getBoundingClientRect();
        const containerW = containerRect.width;
        const containerH = containerRect.height;
        const imageAspect = img.naturalWidth / img.naturalHeight;
        const containerAspect = containerW / containerH;

        let width, height, offsetX, offsetY;

        if (imageAspect > containerAspect) {
            // image is relatively wider -> letterboxed top/bottom
            width = containerW;
            height = containerW / imageAspect;
            offsetX = 0;
            offsetY = (containerH - height) / 2;
        } else {
            // image is relatively taller -> letterboxed left/right
            height = containerH;
            width = containerH * imageAspect;
            offsetY = 0;
            offsetX = (containerW - width) / 2;
        }

        return { rect: containerRect, width, height, offsetX, offsetY };
    };

    const handleMouseMove = (e) => {
        const box = getImageRect();
        if (!box) return;

        const { rect, width, height, offsetX, offsetY } = box;

        // cursor position relative to the actual visible image (not the square container)
        let x = e.clientX - rect.left - offsetX;
        let y = e.clientY - rect.top - offsetY;

        x = Math.max(LENS_SIZE / 2, Math.min(x, width - LENS_SIZE / 2));
        y = Math.max(LENS_SIZE / 2, Math.min(y, height - LENS_SIZE / 2));

        setLensPosition({ x: x + offsetX, y: y + offsetY });
        setCrop({ x: x - LENS_SIZE / 2, y: y - LENS_SIZE / 2 });
        setImgBox({ width, height });
    };

    return (
        <div
            ref={containerRef}
            className="relative w-full aspect-square rounded-lg bg-white"
            onMouseEnter={() => setShowLens(true)}
            onMouseLeave={() => setShowLens(false)}
            onMouseMove={handleMouseMove}
        >
            {/* Product Image */}
            <img
                ref={imgRef}
                src={image}
                alt="Product"
                className="w-full h-full object-contain select-none"
                draggable={false}
            />

            {/* Lens */}
            {showLens && (
                <div
                    className="absolute pointer-events-none border border-gray-400 bg-white/30"
                    style={{
                        width: LENS_SIZE,
                        height: LENS_SIZE,
                        left: lensPosition.x - LENS_SIZE / 2,
                        top: lensPosition.y - LENS_SIZE / 2,
                    }}
                />
            )}

            {/* Zoom Preview Panel — fixed to the right, desktop only */}
            {showLens && imgBox.width > 0 && (
                <div
                    className="absolute left-full ml-10 top-0 hidden lg:block bg-white border rounded-lg shadow-lg overflow-hidden z-50 pointer-events-none"
                    style={{
                        width: PREVIEW_SIZE,
                        height: PREVIEW_SIZE,
                    }}
                >
                    <div
                        style={{
                            width: "100%",
                            height: "100%",
                            backgroundImage: `url("${image}")`,
                            backgroundRepeat: "no-repeat",
                            // exact pixel size/position — no percentage ambiguity,
                            // so the crop under the lens matches the preview 1:1
                            backgroundSize: `${imgBox.width * ZOOM}px ${imgBox.height * ZOOM}px`,
                            backgroundPosition: `-${crop.x * ZOOM}px -${crop.y * ZOOM}px`,
                        }}
                    />
                </div>
            )}
        </div>
    );
}