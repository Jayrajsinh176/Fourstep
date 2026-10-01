import { useEffect, useState } from "react";

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || "https://fourstepretail.com/api";

export default function useReferralDownline() {
  const [rows, setRows] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    let isMounted = true;

    const fetchDownline = async () => {
      try {
        if (isMounted) {
          setError("");
          setIsLoading(true);
        }

        let storedMember = {};
        try {
          storedMember = JSON.parse(localStorage.getItem("memberData") || "{}");
        } catch {
          storedMember = {};
        }
        const userId = storedMember?.user_id;

        if (!userId) {
          throw new Error("Member not found in session. Please sign in again.");
        }

        const response = await fetch(
          `${API_BASE_URL}/downline?user_id=${encodeURIComponent(userId)}`,
          {
            method: "GET",
            headers: {
              Accept: "application/json",
              "X-Auth-Member": userId,
            },
          },
        );

        const data = await response.json();

        if (!response.ok) {
          throw new Error(data?.message || "Unable to fetch referral data");
        }

        const left = Array.isArray(data?.left) ? data.left : [];
        const right = Array.isArray(data?.right) ? data.right : [];

        const merged = [
          ...left.map((member, index) => ({
            ...member,
            sponsored_side: "L",
            _row_key: `L-${member.id || member.user_id || index}`,
          })),
          ...right.map((member, index) => ({
            ...member,
            sponsored_side: "R",
            _row_key: `R-${member.id || member.user_id || index}`,
          })),
        ];

        if (isMounted) {
          setRows(merged);
        }
      } catch (fetchError) {
        if (isMounted) {
          setError(fetchError.message || "Unable to fetch referral data");
          setRows([]);
        }
      } finally {
        if (isMounted) {
          setIsLoading(false);
        }
      }
    };

    fetchDownline();

    return () => {
      isMounted = false;
    };
  }, []);

  return { rows, isLoading, error };
}
