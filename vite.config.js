import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";
import symfonyPlugin from 'vite-plugin-symfony';
import { resolve, dirname } from "path";
import { fileURLToPath } from "url";

const __dirname = dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    plugins: [
        tailwindcss(),
        symfonyPlugin(),
    ],
    build: {
        rollupOptions: {
            input: {
                app: resolve(__dirname, "assets/app.js"),
                chatbot: resolve(__dirname, "assets/chatbot.js"),
            },
        },
    },
    server: {
        origin: "http://localhost:5173",
    },
});
