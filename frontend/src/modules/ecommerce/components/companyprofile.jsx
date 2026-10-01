function CompanyProfile() {
  return (
    <div className="max-w-screen-xl mx-auto px-6 py-16">
      <div className="text-center mb-12">
        <h2 className="text-4xl font-bold text-gray-900">
          <span className="text-red-500">Company</span> Profile
        </h2>
        <div className="w-16 h-1 bg-red-500 mx-auto mt-4 rounded-full" />
      </div>
      <div className="flex flex-col md:flex-row items-center gap-12">
        <div className="md:w-1/2 text-center">
          <img src="/img/pics/Company-profile.jpg" className="w-full rounded-xl" alt="Company Profile" />
        </div>
        <div className="md:w-1/2">
          <p className="text-gray-600 mb-4">
            Fourstep Retail Ltd. is a dynamic and rapidly growing company dedicated to creating opportunities
            for individuals across the nation. With a strong foundation built on ethics, innovation, and
            people-first values, we have established ourselves as a trusted name in the direct selling industry.
          </p>
          <p className="text-gray-600 mb-4">
            Our portfolio spans across Personal Care, Beauty Care, Home Care, and Health Care — offering
            world-class, quality-assured products that make a real difference in everyday lives.
          </p>
          <p className="text-gray-600">
            Fourstep is not just a business — it's a movement that transforms lives by combining a powerful
            business model with a passionate community of achievers.
          </p>
        </div>
      </div>
    </div>
  );
}
export default CompanyProfile;