import { useEffect } from "react";
import { useNavigate } from "react-router-dom";

const SESSION_TIMEOUT = 15 * 60 * 1000; // 15 Minutes

export default function SessionManager() {
  const navigate = useNavigate();

  useEffect(() => {
    let timer;

    const logout = () => {
      localStorage.removeItem("memberSession");
      localStorage.removeItem("memberData");

      navigate("/member/signin", {
        replace: true,
        state: {
          sessionExpired: true,
        },
      });
    };

    const resetTimer = () => {
      clearTimeout(timer);
      timer = setTimeout(logout, SESSION_TIMEOUT);
    };

    const events = [
      "mousemove",
      "mousedown",
      "click",
      "keypress",
      "scroll",
      "touchstart",
      "focus",
    ];

    events.forEach((event) =>
      window.addEventListener(event, resetTimer)
    );

    resetTimer();

    return () => {
      clearTimeout(timer);

      events.forEach((event) =>
        window.removeEventListener(event, resetTimer)
      );
    };
  }, [navigate]);

  return null;
}