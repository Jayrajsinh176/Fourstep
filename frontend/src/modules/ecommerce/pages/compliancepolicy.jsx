import React from "react";



function CompliancePolicy() {
  return (
    <div className="bg-gray-50 min-h-screen py-6 sm:py-8 md:py-10 px-3 sm:px-4">

      <div className="max-w-5xl mx-auto bg-white border border-gray-200 rounded-2xl md:rounded-3xl shadow-sm overflow-hidden">

        {/* HEADER */}
        <div className="bg-gradient-to-r from-gray-50 to-white border-b px-4 sm:px-6 md:px-10 py-6 sm:py-8">

          <h1 className="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900">
            Cancellation Policy
          </h1>

          <p className="text-gray-600 mt-4 leading-7 text-sm sm:text-base">
            At Fourstep, we understand that circumstances can change, and sometimes you may need to cancel an order.
             We aim to make the cancellation process as smooth and convenient as possible.This Cancellation Policy outlines the terms and 
             conditions regarding order cancellations. By shopping with us, you agree to adhere to these policies.
          </p>
        </div>

        {/* CONTENT */}
        <div className="p-4 sm:p-6 md:p-10 space-y-8 md:space-y-10">

          {/* CANCELLATION WINDOW */}
          <section>
            <h2 className="text-xl sm:text-2xl font-semibold mb-5 text-gray-900">
              Cancellation Window
            </h2>

            <div className="border border-gray-200 rounded-xl md:rounded-2xl p-4 sm:p-6 bg-gray-50 text-gray-700 leading-7 sm:leading-8 text-sm sm:text-base">
              You may request the cancellation of your order within 24 hours of placing it.
              We recommend reviewing your order carefully during this time to ensure it meets your requirements.
            </div>
          </section>

          {/* CANCELLATION PROCESS */}
          <section>
            <h2 className="text-xl sm:text-2xl font-semibold mb-5 text-gray-900">
              Cancellation Process
            </h2>

            <div className="border border-gray-200 rounded-xl md:rounded-2xl p-4 sm:p-6 bg-gray-50 text-gray-700 leading-7 sm:leading-8 text-sm sm:text-base">
              To cancel an order, please contact our customer support team at 9726286000 as soon as possible.
              Provide your order number and the reason for cancellation.
            </div>
          </section>

          {/* REFUND */}
          <section>
            <h2 className="text-xl sm:text-2xl font-semibold mb-5 text-gray-900">
              Refunds for Cancelled Orders
            </h2>

            <div className="border border-gray-200 rounded-xl md:rounded-2xl p-4 sm:p-6 bg-gray-50 text-gray-700 leading-7 sm:leading-8 text-sm sm:text-base">
              If your cancellation request is made within the specified cancellation window,
              we will process a full refund to your original payment method.
              Refunds may take 20 business days to reflect in your account,
              depending on your financial institution’s policies.
            </div>
          </section>

          {/* LATE CANCELLATION */}
          <section>
            <h2 className="text-xl sm:text-2xl font-semibold mb-5 text-gray-900">
              Late Cancellation
            </h2>

            <div className="border border-gray-200 rounded-xl md:rounded-2xl p-4 sm:p-6 bg-gray-50 text-gray-700 leading-7 sm:leading-8 text-sm sm:text-base">
              If you request a cancellation after the specified cancellation window,
              no refund will be issued.
            </div>
          </section>

          {/* CONTACT */}
          <section>
            <h2 className="text-xl sm:text-2xl font-semibold mb-5 text-gray-900">
              Contact Us
            </h2>

            <div className="border border-gray-200 rounded-xl md:rounded-2xl p-4 sm:p-6 bg-gray-50 text-gray-700 leading-7 sm:leading-8 text-sm sm:text-base">

              If you have any questions or concerns regarding our Cancellation Policy
              or need assistance with cancelling an order, please contact our customer support team.
              Email:<b> support@fourstepretail.com</b> or contacting us on <b>+91 97262 86000</b>.
              We are here to assist you with any cancellation-related inquiries.
            </div>
          </section>

          {/* POLICY CHANGES */}
          <section>
            <h2 className="text-xl sm:text-2xl font-semibold mb-5 text-gray-900">
              Policy Changes
            </h2>

            <div className="border border-gray-200 rounded-xl md:rounded-2xl p-4 sm:p-6 bg-gray-50 text-gray-700 leading-7 sm:leading-8 text-sm sm:text-base">
              Fourstep reserves the right to update this Cancellation Policy.
              Any changes will be posted on our website, and we encourage you to review this policy periodically.
            </div>
          </section>

          {/* THANK YOU */}
          <section>
            <h2 className="text-xl sm:text-2xl font-semibold mb-5 text-gray-900">
              Thank You for Shopping with Fourstep
            </h2>

            <div className="border border-gray-200 rounded-xl md:rounded-2xl p-4 sm:p-6 bg-gray-50 text-gray-700 leading-7 sm:leading-8 text-sm sm:text-base">
              We appreciate your trust in Fourstep for your shopping needs.
              We strive to provide excellent service and a hassle-free shopping experience.
              If you have any questions or need further assistance,
              please do not hesitate to contact us.
            </div>
          </section>

        </div>
      </div>
    </div>
  );
}


export default CompliancePolicy;