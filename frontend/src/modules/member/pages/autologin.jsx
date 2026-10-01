import { useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import axios from "axios";

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || "https://fourstepretail.com/api";

export default function AutoLogin() {
  const { id } = useParams();
  const navigate = useNavigate();

  useEffect(() => {
    axios
      .post(
        `${API_BASE_URL}/member-auto-login`,
        { id },
        {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
        }
      )
      .then((res) => {
        const { user_id } = res.data;

        if (!user_id) {
          console.error(
            "❌ AutoLogin: user_id missing in response",
            res.data
          );
          alert("Auto-login failed: no user_id returned");
          return;
        }

        localStorage.removeItem("member_user_id");
        localStorage.removeItem("memberData");
        localStorage.removeItem("memberSession");

        const member = res.data.member || res.data;

        localStorage.setItem(
          "member_user_id",
          String(member.user_id)
        );

        localStorage.setItem(
          "memberData",
          JSON.stringify(member)
        );

        localStorage.setItem("memberSession", "true");

        navigate("/member/dashboard", { replace: true });
      })
      .catch((err) => {
        console.error(
          "❌ AutoLogin API error:",
          err.response?.data || err.message
        );

        alert(
          "Auto-login failed: " +
          (err.response?.data?.error || err.message)
        );
      });
  }, [id, navigate]);

  return <h2>Logging in...</h2>;
}