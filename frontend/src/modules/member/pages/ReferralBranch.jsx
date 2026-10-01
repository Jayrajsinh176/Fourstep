import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import ReferralTableCard from "../components/ReferralTableCard";
import { useEffect, useState } from "react";

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || "https://fourstepretail.com/api";

export default function ReferralBranch() {
  const [rows, setRows] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    let isMounted = true;

    const fetchBranches = async () => {
      try {
        if (isMounted) {
          setIsLoading(true);
          setError("");
        }

        let storedMember = {};
        try {
          storedMember =
            JSON.parse(localStorage.getItem("memberData") || "{}") || {};
        } catch {
          storedMember = {};
        }

        const memberUserId = storedMember?.user_id || "";
        if (!memberUserId) {
          throw new Error("Member not found in session. Please sign in again.");
        }

        const query = `?user_id=${encodeURIComponent(memberUserId)}`;

        const response = await fetch(
          `${API_BASE_URL}/branches/referral${query}`,
          {
            method: "GET",
            headers: {
              Accept: "application/json",
              "X-Auth-Member": memberUserId,
            },
          },
        );

        const data = await response.json();

        if (!response.ok) {
          throw new Error(data?.message || "Unable to fetch branch data");
        }

        if (isMounted) {
          setRows(Array.isArray(data?.data) ? data.data : []);
        }
      } catch (fetchError) {
        if (isMounted) {
          setError(fetchError.message || "Unable to fetch branch data");
          setRows([]);
        }
      } finally {
        if (isMounted) {
          setIsLoading(false);
        }
      }
    };

    fetchBranches();

    return () => {
      isMounted = false;
    };
  }, []);

  const columns = [
    {
      key: "sr_no",
      header: "Sr No",
      render: (_, index) => String(index + 1).padStart(2, "0"),
    },
    {
      key: "shopee_name",
      header: "Shopee Name",
      render: (row) => row.shopee_name || "--",
    },
    {
      key: "user_id",
      header: "User ID",
      render: (row) => row.user_id || "--",
    },
    {
      key: "branch_type",
      header: "Branch Type",
      render: (row) => row.branch_type || row.shopee_type || "--",
    },
    {
      key: "date",
      header: "Date",
      render: (row) => row.date || "--",
    },
    {
      key: "contact_person",
      header: "Contact Person",
      render: (row) => row.contact_person || "--",
    },
    {
      key: "state",
      header: "State",
      render: (row) => row.state || "--",
    },
    {
      key: "district",
      header: "District",
      render: (row) => row.district || "--",
    },
    {
      key: "city_taluka",
      header: "City/Taluka",
      render: (row) => row.city_taluka || "--",
    },
    {
      key: "status",
      header: "Status",
      render: (row) => row.status || "--",
    },
  ];

  return (
    <div className="flex flex-col lg:flex-row bg-gray-100 min-h-screen">
      <Sidebar />

      <div className="flex-1 min-w-0 flex flex-col">
        <Navbar />

        <ReferralTableCard
          title="Referral Branch"
          columns={columns}
          rows={rows}
          isLoading={isLoading}
          error={error}
          emptyMessage="No referral branch records found"
        />
      </div>
    </div>
  );
}
