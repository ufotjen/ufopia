// resources/js/bootstrap.ts

// Axios (HTTP) — standaard Laravel setup
import axios from "axios"

axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest"

// CSRF-token uit meta tag (Laravel zet deze in app.blade.php)
const token = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("content")

if (token) {
    axios.defaults.headers.common["X-CSRF-TOKEN"] = token
}

window.axios = axios

// ---- Optioneel (later): Laravel Echo/Pusher ----
// import Echo from "laravel-echo"
// // @ts-expect-error
// window.Pusher = require("pusher-js")
// // @ts-expect-error
// window.Echo = new Echo({
//   broadcaster: "pusher",
//   key: import.meta.env.VITE_PUSHER_APP_KEY,
//   wsHost: import.meta.env.VITE_PUSHER_HOST ?? window.location.hostname,
//   wsPort: Number(import.meta.env.VITE_PUSHER_PORT ?? 6001),
//   wssPort: Number(import.meta.env.VITE_PUSHER_PORT ?? 6001),
//   forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? "https") === "https",
//   enabledTransports: ["ws", "wss"],
// })
