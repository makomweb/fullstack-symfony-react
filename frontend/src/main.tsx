import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import "./styles/index.css";
import App from "./App.tsx";
import "@fontsource/roboto/300.css";
import "@fontsource/roboto/400.css";
import "@fontsource/roboto/500.css";
import "@fontsource/roboto/700.css";
import { setupOTelSDK, setupFetchInstrumentation } from "@makomweb/otel-sdk-react";
import { BACKEND_API_URL } from "./config/env.ts";

// Initialize OTEL infrastructure with explicit collector address
const collectorAddress = (window as any).OTEL_COLLECTOR_ADDRESS || "http://localhost:4318";
setupOTelSDK(collectorAddress);

// Set up application-specific fetch tracing with backend URL
setupFetchInstrumentation(BACKEND_API_URL);

createRoot(document.getElementById("root")!).render(
  <StrictMode>
    <App />
  </StrictMode>,
);
