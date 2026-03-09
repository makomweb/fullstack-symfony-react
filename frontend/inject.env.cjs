const fs = require("fs");
const { parseArgs } = require("node:util");

const { BACKEND_API_URL, OTEL_COLLECTOR_ADDRESS } = process.env;

const args = process.argv;
const options = {
  indexPath: {
    type: "string",
  },
};
const { values } = parseArgs({
  args,
  options,
  allowPositionals: true,
});

const { indexPath } = values;

fs.readFile(indexPath, "utf8", (err, data) => {
  if (err) {
    return console.error("Error reading index.html:", err);
  }

  const withBackendApiUrl = data.replace(
    '<script id="BACKEND_API_URL-placeholder"></script>',
    `<script>window.BACKEND_API_URL = "${BACKEND_API_URL}";</script>`,
  );

  const withCollectorAddress = withBackendApiUrl.replace(
    '<script id="OTEL_COLLECTOR_ADDRESS-placeholder"></script>',
    `<script>window.OTEL_COLLECTOR_ADDRESS = "${OTEL_COLLECTOR_ADDRESS}";</script>`,
  );

  fs.writeFile(indexPath, withCollectorAddress, "utf8", (err) => {
    if (err) {
      return console.error("Error writing index.html:", err);
    }
    console.log("Environment variable injected successfully.");
  });
});
