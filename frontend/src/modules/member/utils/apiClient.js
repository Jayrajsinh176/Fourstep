const BASE_URL = (import.meta.env.VITE_API_BASE_URL || "").trim();

export async function requestMemberApi(path, methodOrOptions = "GET", data = null) {
  const url = BASE_URL + (path.startsWith("/") ? path : `/${path}`);

  console.log("👉 API CALL:", url);

  let options = {};

  // ✅ Case 1: Old style (options object)
  if (typeof methodOrOptions === "object") {
    options = methodOrOptions;
  }

  // ✅ Case 2: New style (method + data)
  else {
    options = {
      method: methodOrOptions || "GET",
      headers: {
        "Content-Type": "application/json",
      },
    };

    if (data) {
      options.body = JSON.stringify(data);
    }
  }

  const response = await fetch(url, options);

  let resData = {};
  try {
    resData = await response.json();
  } catch {}

  return {
    ok: response.ok,
    status: response.status,
    data: resData,
  };
}