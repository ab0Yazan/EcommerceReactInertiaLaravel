import { useEffect, useState } from "react";

function Carousal({ images }) {
    const [selectedImageIndex, setSelectedImageIndex] = useState(0);

    useEffect(() => {
        setSelectedImageIndex(0);
    }, [images]);

    return (
        <div className="flex items-start gap-8">
            {/* Thumbnails */}
            <div className="flex flex-col items-center gap-2 py-2">
                {images.map((image, index) => (
                    <button
                        key={image.id}
                        onClick={() => setSelectedImageIndex(index)}
                        className={`border-2 rounded-md ${
                            selectedImageIndex === index ? "border-blue-500" : "border-transparent"
                        } hover:border-blue-400`}
                    >
                        <img src={image.thumb} alt={image.name} className="w-[50px]" />
                    </button>
                ))}
            </div>

            {/* Main Image */}
            <div className="w-full border-4 rounded-lg">
                <img
                    src={images[selectedImageIndex]?.large}
                    alt={images[selectedImageIndex]?.name}
                    className="w-full object-contain rounded"
                />
            </div>
        </div>
    );
}

export default Carousal;
