import { Resource } from "@opentelemetry/resources";
import { SEMRESATTRS_SERVICE_NAME } from "@opentelemetry/semantic-conventions";
import {
  SimpleSpanProcessor,
  WebTracerProvider,
} from "@opentelemetry/sdk-trace-web";
import * as OTEL_API from "@opentelemetry/api";
import * as LOGS_API from "@opentelemetry/api-logs";
import { FetchInstrumentation } from "@opentelemetry/instrumentation-fetch";
import { registerInstrumentations } from "@opentelemetry/instrumentation";
import { DocumentLoadInstrumentation } from "@opentelemetry/instrumentation-document-load";
import {
  MeterProvider,
  PeriodicExportingMetricReader,
} from "@opentelemetry/sdk-metrics";

import { OTLPTraceExporter } from "@opentelemetry/exporter-trace-otlp-http";
import { OTLPMetricExporter } from "@opentelemetry/exporter-metrics-otlp-http";
import { OTLPLogExporter } from "@opentelemetry/exporter-logs-otlp-http";
import * as SDK_LOGS from "@opentelemetry/sdk-logs";
import { BACKEND_API_URL, OTEL_COLLECTOR_ADDRESS } from "./env";

const setupOTelSDK = () => {
  const resource = Resource.default().merge(
    new Resource({
      [SEMRESATTRS_SERVICE_NAME]: "frontend",
    }),
  );

  // TRACES
  const tracerProvider = new WebTracerProvider({
    resource: resource,
  });

  const traceExporter = new OTLPTraceExporter({
    url: `${OTEL_COLLECTOR_ADDRESS}/v1/traces`,
    headers: {},
  });

  const spanProcessor = new SimpleSpanProcessor(traceExporter);

  // METRICS
  const metricExporter = new OTLPMetricExporter({
    url: `${OTEL_COLLECTOR_ADDRESS}/v1/metrics`,
    headers: {},
  });
  const metricReader = new PeriodicExportingMetricReader({
    exporter: metricExporter,
    // Default is 60000ms (60 seconds). Set to 10 seconds for demonstrative purposes only.
    exportIntervalMillis: 10000,
  });

  const meterProvider = new MeterProvider({
    resource: resource,
    readers: [metricReader],
  });

  OTEL_API.metrics.setGlobalMeterProvider(meterProvider);

  // LOGS
  const logExporter = new OTLPLogExporter({
    url: `${OTEL_COLLECTOR_ADDRESS}/v1/logs`,
    headers: {},
  });

  const logProcessor = new SDK_LOGS.SimpleLogRecordProcessor(logExporter);
  const loggerProvider = new SDK_LOGS.LoggerProvider();
  loggerProvider.addLogRecordProcessor(logProcessor);
  LOGS_API.logs.setGlobalLoggerProvider(loggerProvider);

  tracerProvider.addSpanProcessor(spanProcessor);
  tracerProvider.register();
  OTEL_API.trace.setGlobalTracerProvider(tracerProvider);

  registerInstrumentations({
    instrumentations: [
      new FetchInstrumentation({
        propagateTraceHeaderCorsUrls: [
          new RegExp(`${BACKEND_API_URL.replace("/", "\/")}\/.*`),
        ],
      }),
      new DocumentLoadInstrumentation(),
    ],
  });
};

export { setupOTelSDK };
