import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";

// Plugin to inject environment variables into index.html
function injectEnvPlugin() {
  return {
    name: "inject-env",
    transformIndexHtml(html: string) {
      const backendApiUrl = process.env.BACKEND_API_URL || "http://localhost:8080/api";
      const otelCollectorAddress = process.env.OTEL_COLLECTOR_ADDRESS || "http://localhost:4318";

      return html
        .replace(
          '<script id="BACKEND_API_URL-placeholder"></script>',
          `<script>window.BACKEND_API_URL = "${backendApiUrl}";</script>`
        )
        .replace(
          '<script id="OTEL_COLLECTOR_ADDRESS-placeholder"></script>',
          `<script>window.OTEL_COLLECTOR_ADDRESS = "${otelCollectorAddress}";</script>`
        );
    },
  };
}

// https://vite.dev/config/
export default defineConfig({
  plugins: [injectEnvPlugin(), react()],
});
