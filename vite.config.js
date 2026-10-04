import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/js/customer-signup.js",
                "resources/js/customer-dashboard.js",
            ],
            refresh: true,
        }),
    ],

    server: {
        host: "0.0.0.0",
        port: 5173,
        origin: "http://192.168.1.136:5173",

        hmr: {
            host: "192.168.1.136",
            port: 5173,
        },
    },
});
