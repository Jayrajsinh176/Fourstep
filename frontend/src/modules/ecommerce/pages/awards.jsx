import { useState } from "react";

const documents = [
  {
    id: 1,
    title: "Cricket Championship ",
    image: "/images/award.jpg",
  },
  {
    id: 2,
    title: "Franchise Excellence",
    image: "/images/award2.jpg",
  },
  {
    id: 3,
    title: "Global Legends",
    image: "/images/award3.jpg",
  },
  {
    id: 4,
    title: "GPBS-25 Achievement",
    image: "/images/award4.jpg",
  },
  {
    id: 5,
    title: "Heights of Success",
    image: "/images/award5.jpg",
  },
  {
    id: 6,
    title: "Excellence Award",
    image: "/images/award6.jpg",
  },
    {
    id: 7,
    title: "Leadership Recognition",
    image: "/images/award7.jpg",
  },
];

const DocumentCard = ({ doc }) => {
  const [hovered, setHovered] = useState(false);

  return (
    <div
      className={`bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col transition-shadow duration-200 ${
        hovered ? "shadow-lg" : "shadow-sm"
      }`}
      onMouseEnter={() => setHovered(true)}
      onMouseLeave={() => setHovered(false)}
    >
      {/* Image Area */}
      <div className="h-full w-full overflow-hidden bg-gray-50">
        <img
          src={doc.image}
          alt={doc.title}
          className="w-full h-full object-contain"
        />
      </div>

      {/* Info */}
      <div className="px-4 py-3 pb-4 border-t border-gray-100">
        <p className="text-lg font-semibold text-blue-600">{doc.title}</p>
      </div>
    </div>
  );
};

export default function Awards() {
  const firstRow = documents.slice(0, 4);
  const secondRow = documents.slice(4, 7);

  return (
    <div className="min-h-screen bg-gray-50 p-4 sm:p-6 lg:p-8">
      <div className="max-w-7xl mx-auto">
        <h2 className="text-2xl text-center font-semibold text-blue-700 underline underline-offset-4 mb-5">Our Achievements & Recognition</h2>

        {/* Row 1 — 3 cards */}
        <div className="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-4 text-center font-semibold">
          {firstRow.map((doc) => (
            <DocumentCard key={doc.id} doc={doc} />
          ))}
        </div>

        {/* Row 2 — 2 cards centered */}
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:w-2/3 mx-auto text-center font-semibold">
          {secondRow.map((doc) => (
            <DocumentCard key={doc.id} doc={doc} />
          ))}
        </div>
      </div>
    </div>
  );
}