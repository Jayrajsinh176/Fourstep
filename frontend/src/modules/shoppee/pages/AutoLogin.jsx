import { useEffect } from "react";
import { useNavigate, useSearchParams } from "react-router-dom";

export default function AutoLogin() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();

  useEffect(() => {
    const user = searchParams.get("user");

    if (!user) {
      navigate("/shoppee/signin");
      return;
    }

    try {
      const parsedUser = JSON.parse(atob(user));

      localStorage.setItem(
        "user",
        JSON.stringify(parsedUser)
      );

      navigate("/shoppee/dashboard");
    } catch (error) {
      navigate("/shoppee/signin");
    }
  }, []);

  return (
    <div className="flex justify-center items-center min-h-screen">
      <h2>Logging in...</h2>
    </div>
  );
}