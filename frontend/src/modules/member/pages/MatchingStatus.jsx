import { useEffect, useState } from "react";
import axios from "axios";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";

export default function MatchingStatus() {
  const [data, setData] = useState([]);

  useEffect(() => {
    fetchMatching();
  }, []);

 const fetchMatching = async () => {
  try {
    const member = JSON.parse(
      localStorage.getItem("memberData") || "{}"
    );
console.log("Member Data:", member);
    const res = await axios.get(
      "https://fourstepretail.com/api/matching-status",
      {
        headers: {
          "X-Auth-Member": member.user_id,
        },
      }
    );

    setData(res.data);
  } catch (error) {
    console.error("Error fetching matching:", error);
  }
};

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />

      <div className="flex-1 min-w-0 flex flex-col">
        <Navbar />

        <div className="text-center mt-6">
          <h1 className="text-3xl font-bold text-[#B0422E]">My Group Turnover Status</h1>
        </div>

        <div className="p-6">
          <div className="bg-white rounded-2xl shadow-md p-8">
            <div className="overflow-x-auto">
              <table className="w-full text-sm text-center">
                <thead>
                  <tr className="bg-[#B0422E] text-white font-semibold">
                    <th rowSpan="2" className="py-4 px-6 rounded-l-xl">
                      Sr No
                    </th>
                    <th rowSpan="2" className="py-4 px-6">
                      Date
                    </th>
                    <th
                      colSpan="2"
                      className="py-4 px-6 border-l border-white/40"
                    >
                      Forward
                    </th>
                    <th
                      colSpan="2"
                      className="py-4 px-6 border-l border-white/40"
                    >
                      Current
                    </th>
                    <th
                      colSpan="2"
                      className="py-4 px-6 border-l border-white/40"
                    >
                      Total
                    </th>
                    <th
                      rowSpan="2"
                      className="py-4 px-6 border-l border-white/40"
                    >
                      Total Matching
                    </th>
                    <th
                      rowSpan="2"
                     className="py-4 px-6 rounded-r-xl border-l border-white/40"
                    >
                      Qualified Matching
                    </th>
                    {/* <th
  rowSpan="2"
  className="py-4 px-6 rounded-r-xl border-l border-white/40"
>
  Cycle
</th> */}
                  </tr>

                  <tr className="bg-[#B0422E] text-white font-semibold">
                    <th className="py-3 px-6 border-l border-white/40">Left</th>
                    <th className="py-3 px-6">Right</th>

                    <th className="py-3 px-6 border-l border-white/40">Left</th>
                    <th className="py-3 px-6">Right</th>

                    <th className="py-3 px-6 border-l border-white/40">Left</th>
                    <th className="py-3 px-6">Right</th>
                  </tr>
                </thead>

                <tbody className="text-black font-medium">
                  {data.length > 0 ? (
                    data.map((row, index) => {
                      const forwardLeft = row.carry_forward_left;
                      const forwardRight = row.carry_forward_right;

                      const currentLeft = row.left_bv - forwardLeft;
                      const currentRight = row.right_bv - forwardRight;

                      return (
                        <tr key={index} className="border-b border-gray-300">
                          <td className="py-5">{index + 1}</td>
                          <td>{row.match_date}</td>

                          <td>{forwardLeft}</td>
                          <td>{forwardRight}</td>

                          <td>{currentLeft}</td>
                          <td>{currentRight}</td>

                          <td>{row.left_bv}</td>
                          <td>{row.right_bv}</td>

                         <td>{row.matched_pv}</td>
                          <td>{row.income_generated}</td>
                          {/* <td>{row.cycle === 1 ? "Cycle 1" : "Cycle 2"}</td> */}
                        </tr>
                      );
                    })
                  ) : (
                    <tr>
                      <td colSpan="11" className="py-6">
                        No Matching Data Found
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>

            {/* Total Summary */}
            <div className="mt-8 text-center font-semibold text-[#151D28]">
              Total Matching Income:{" "}
              {data.reduce(
                (sum, row) => sum + parseFloat(row.income_generated || 0),
                0,
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
