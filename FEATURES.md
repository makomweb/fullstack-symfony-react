# Features

- 🚀 Backend 
    - ✅ [Symfony 8.x](https://symfony.com/doc/current/setup.html)
    - ✅ Asynchronous worker via Symfony Messenger
    - ✅ [hexagonal architecture](https://alistair.cockburn.us/hexagonal-architecture/)
    - ✅ [CQRS](https://martinfowler.com/bliki/CQRS.html) + [event sourcing](https://martinfowler.com/eaaDev/EventSourcing.html)
    - ✅ use [AutoMapper](https://automapper.jolicode.com) for DTO to Command mapping
    - ✅ tactial DDD (annotations + check command)
    - ✅ API documentation with Swagger / [Open API specification](https://swagger.io/specification/)
    - ✅ use [RabbitMQ](https://www.rabbitmq.com/) as message queue between "app" and "worker"
    - ✅ 100 % coverage in the core domain
- 🚀 Frontend
    - ✅ [Vite](https://vite.dev/)
    - ✅ [Typescript](https://www.typescriptlang.org/)
    - ✅ [React 19](https://react.dev/reference/react)
    - ✅ [Material UI v6](https://mui.com/material-ui/getting-started/)
    - ✅ alternative UI [PHP + Twig + Bootstrap](https://getbootstrap.com/)
- ✅ [Redis](https://redis.io) as shared cache
    - ✅ Store sessions into the shared cache for scaling the backend
- 🚀 Containerization
    - 🐳 [Docker](https://www.docker.com/) multistage build for dev + prod
    - ⛴️ [Kubernetes](https://kubernetes.io/) via [Helm](https://helm.sh/)
        - ✅ Backend + worker
        - ✅ Dashboard + Telemetry
        - ✅ Frontend
- ✅ Observability
    - ✅ [Grafana](https://grafana.com/) dashboard with the [LGTM stack](https://grafana.com/go/webinar/getting-started-with-grafana-lgtm-stack/?pg=webinar-intro-to-ci-cd-observability-and-the-grafana-lgtm-stack&plcmt=featured-videos-2)
    - ✅  [Open Telemetry](https://opentelemetry.io/docs/what-is-opentelemetry/) with centralized otel-collector
    - ✅ [Loki](https://grafana.com/oss/loki/) for logging
    - ✅ [Tempo](https://grafana.com/oss/tempo/) for tracing
    - ✅ [Prometheus](https://prometheus.io/) for metrics
- ✅ [CI via Github actions](https://docs.github.com/en/actions/about-github-actions/about-continuous-integration-with-github-actions)
    - ✅ PhpUnit 13
    - ✅ Vitest
- ✅ [CloudEvent](https://github.com/cloudevents/spec/blob/v1.0.2/cloudevents/spec.md) specification
- ✅ Authentication
    - ✅ TWIG app
    - ✅ React app
    - ✅ CORS
    - ✅ Remember Me
- ✅ Authorization
    - ✅ check permissions (configured in YAML) via Symfony voter
- ✅ arc42 documentation template
- ✅ add architecture visualization to Symfony Profiler

## Planned

- ❌ Multi tenancy
- ❌ Connect an example legacy application to the modern application
    - ❌ Webhooks
    - ❌ CRUD based DB model (in contrast to event sourcing and CQRS)
- ❌ Class diagram / dependency diagram for src/ via analyze CLI command + Graphiz Dot
- ❌ Kubernetes: 
    - ❌ Readyness/liveness probes
    - ❌ Put worker into a Deployment instead of a single pod
    - ❌ Provision Grafana dashboard, e.g. via ConfigMap
    - ❌ Deploy DB via stateful set
- ❌ Include Alpine image into Docker multistage build for production

# Unify login

- current state::
    - legacy app: 
        - uses form_login (see security.yaml)
        - a "non-admin" user logs in via route app.login (see: backend/src/Controller/LoginController.php
    - admin panel:
        - uses form_login (see security.yaml)
        - a "admin" user logs uses the same login controller to access the admin panel
        - the admin panel is secured via firewall - the user must have ROLE_ADMIN
    - modern app:
        - uses json_login (see security.yaml)
        - provides a login form via React frontend

- vision:
    - legacy app:
        - keep form_login as it is
        - when regular user logs in redirect to the GameController::index() (as it is currently)
        - when admin logs in redirect to the DashboardController::index() if possible (if it is too hard to implement - don't implement the redirect)
        - keep the admin route for explicit admin login
    - modern app:
        - get inspiration from the split-fairly app
            - this is a sibling project to this project
            - it uses explicit vite build docker compose service
            - it provides a highly customized SpaController
        - uses form_login now
        - check if we can reuse the existing TWIG form from the legacy app        