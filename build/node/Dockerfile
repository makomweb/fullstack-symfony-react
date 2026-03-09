# Build-Stage
FROM node:23-alpine AS build

WORKDIR /app

# Abhängigkeiten installieren
COPY frontend/package.json .
COPY frontend/package-lock.json* .
RUN npm ci

# Quellcode kopieren und bauen
COPY frontend/src ./src
COPY frontend/tests ./tests
COPY frontend/.eslintrc.cjs .
COPY frontend/.prettier.rc .
COPY frontend/eslint.config.js .
COPY frontend/index.html .
COPY frontend/tsconfig.app.json .
COPY frontend/tsconfig.json .
COPY frontend/tsconfig.node.json .
COPY frontend/vite.config.ts .
COPY frontend/vitest.config.ts .

RUN npm run build

# Produktions-Stage
FROM node:23-alpine AS prod

WORKDIR /app

# Setze Node auf Produktionsmodus
ENV NODE_ENV=production

# Installiere serve zum Hosten der statischen Dateien
RUN npm install -g serve

# Kopiere die Build-Dateien von der Build-Stage
COPY --from=build /app/dist ./dist
COPY frontend/inject.env.cjs .

# Port 3500 für die App
EXPOSE 3500

# Starte die Anwendung und injiziere die Umgebungsvariable
CMD ["sh", "-c", "node inject.env.cjs --indexPath=./dist/index.html && serve -s dist -l 3500"]