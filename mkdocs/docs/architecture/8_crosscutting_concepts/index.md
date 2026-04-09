# Cross-cutting Concepts 

## Single Page Application (SPA) Serving

### Overview

The React application is served differently depending on the environment:

**Development:** Vite dev server (port 8090) with Hot Module Reload (HMR) for fast feedback
**Production:** Symfony backend (port 8080) serves compiled React app to authenticated users

### Architecture

Two entry points ensure optimal developer experience and production efficiency:

| Environment | Entry Point | Used By | Script | Features |
|-------------|------------|---------|--------|----------|
| **Development** | `frontend/index.html` | Vite dev server (8090) | `npm run dev` | HMR enabled, TypeScript source |
| **Production** | `backend/templates/spa/index.html.twig` | SpaController (8080) | `npm run build` | Compiled bundle, dynamic config |

### Development Flow (Port 8090)

```
Browser (8090)
  ↓
Vite dev server loads: frontend/index.html
  ↓
Renders React from: /src/main.tsx (TypeScript source)
  ↓
Hardcoded config for localhost development
  ↓
Hot Module Reload active - instant feedback on code changes
```

**Benefits:**
- Instant feedback loop (HMR refreshes browser on file changes)
- Source maps available for debugging
- TypeScript compilation in-memory
- No rebuild step needed per change

### Production Flow (Port 8080)

```
Browser (8080/spa)
  ↓
Symfony backend (SpaController)
  ↓
Renders: backend/templates/spa/index.html.twig
  ↓
Twig injects dynamic environment config
  ↓
Loads compiled React from: /dist/main.js (production bundle)
  ↓
Static asset serving - no HMR
```

**Benefits:**
- Optimized bundle (Vite build output)
- Dynamic configuration at runtime
- Same origin - no CORS issues
- Session-based authentication for all users

### Key Differences

| Aspect | Development | Production |
|--------|-------------|-----------|
| **Served by** | Vite dev server | Symfony backend |
| **Port** | 8090 | 8080 |
| **Config** | Hardcoded in HTML | Injected by Twig |
| **Source** | TypeScript (`/src/`) | Compiled (`/dist/`) |
| **HMR** | ✅ Enabled | ❌ Disabled |
| **Purpose** | Developer productivity | Optimized delivery |

### Related Files

- **Development:** `frontend/index.html`, `frontend/src/main.tsx`, `frontend/vite.config.ts`
- **Production:** `backend/templates/spa/index.html.twig`, `backend/src/Controller/SpaController.php`
- **Build:** `build/node/Dockerfile` (Node build stage for Vite compilation)
- **Development Config:** `docker-compose.yaml` (frontend service with Vite)

---

## Observability & Distributed Tracing (OpenTelemetry)

### Overview

This system implements comprehensive observability across all services using **OpenTelemetry (OTel)**, an open standard for collecting telemetry data (traces, metrics, logs) from distributed systems. The implementation enables correlation of requests as they flow through the frontend, backend, and asynchronous worker services.

### Architecture

The observability stack consists of:

- **Frontend (React):** WebTracerProvider with FetchInstrumentation for HTTP request tracing
- **Backend (Symfony):** Auto-instrumented PHP application with PSR3 (logging), PSR15 (HTTP middleware), and PSR18 (HTTP client) support
- **Worker (PHP/RabbitMQ):** Async message processing with trace context propagation via EventPersisted messages
- **Collector (LGTM Stack):** OpenTelemetry Collector receiving OTLP protocol data on port 4318
- **Storage & Visualization:** Tempo for traces, Prometheus for metrics, Loki for logs, Grafana for unified dashboards

### Trace Context Propagation

Traces flow through the system using **W3C Trace Context** standard:

1. **Frontend to Backend:** HTTP requests include `traceparent` and `tracestate` headers via FetchInstrumentation
2. **Backend Processing:** TraceContextGetter extracts incoming headers; TraceSpan creates child spans maintaining parent-child relationships
3. **Async Processing:** EventStoredNotifier injects trace context into EventPersisted messages; EventPersistedHandler extracts context on the worker
4. **All Spans:** Exported to OTLP collector via OTLPTraceExporter

### Configuration

| Component | Service Name | Exporter | Endpoint |
|-----------|--------------|----------|----------|
| Frontend | `frontend` | OTLPTraceExporter (HTTP) | http://localhost:4318/v1/traces |
| Backend | `backend` | OTLPTraceExporter (HTTP) | http://localhost:4318/v1/traces |
| Worker | `worker` | OTLPTraceExporter (HTTP) | http://localhost:4318/v1/traces |

### Auto-Instrumentation

**Backend & Worker (PHP):**
- `open-telemetry/instrumentation-symfony` - HTTP middleware tracing
- `open-telemetry/instrumentation-psr3` - Logging integration
- `open-telemetry/instrumentation-psr15` - HTTP middleware
- `open-telemetry/instrumentation-psr18` - HTTP client calls

**Frontend (React):**
- `@opentelemetry/instrumentation-fetch` - HTTP request tracing with CORS header propagation
- `@opentelemetry/instrumentation-document-load` - Page load timing and resource performance

### Enabling Observability

The observability system is controlled by environment variables:

```
APP_TELEMETRY=Otel              # Enable OTel (alternative: Stdout, Noop)
OTEL_SERVICE_NAME=<service>     # frontend, backend, or worker
OTEL_TRACES_EXPORTER=otlp       # Use OTLP exporter
OTEL_EXPORTER_OTLP_ENDPOINT=... # Collector endpoint
```

For the frontend:
```typescript
// frontend/src/main.tsx
import { setupOTelSDK } from "./config/otel.ts";
setupOTelSDK(); // Initialize before React renders
```

### Use Cases

1. **Performance Monitoring:** Track request latency from frontend through backend to worker completion
2. **Error Tracking:** Correlate error logs with exact trace context across services
3. **Debugging:** Visualize complete request flow and identify bottlenecks
4. **Service Dependencies:** Understand how services interact via trace data
5. **Business Metrics:** Custom spans can track business logic execution

### Viewing Traces

1. Open Grafana: http://localhost:3000
2. Navigate to Explore → Select Tempo datasource
3. Query by service name (frontend, backend, worker) or trace ID
4. Click trace to see full span hierarchy and parent-child relationships
5. Cross-reference with logs and metrics using trace ID correlation

### Standards & Specifications

- **Trace Context:** W3C Trace Context ([specification](https://www.w3.org/TR/trace-context/))
- **Semantic Conventions:** OpenTelemetry Semantic Conventions for consistent span attributes
- **OTLP Protocol:** OpenTelemetry Protocol for exporting telemetry data

### Related Files

- **Frontend:** `frontend/src/config/otel.ts` (SDK configuration), `frontend/src/main.tsx` (initialization)
- **Backend:** `backend/src/Instrumentation/Otel/` (tracer, metrics, logging providers)
- **Backend Listeners:** `TraceContextGetter`, `EventStoredNotifier`, `EventPersistedHandler`
- **Infrastructure:** `docker-compose.yaml` (LGTM stack configuration)
- **Dashboards:** `grafana/dashboards/logs-traces-metrics.json` (pre-built Grafana dashboard)