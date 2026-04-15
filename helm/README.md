# Fullstack Symfony + React Helm Chart

Consolidated Kubernetes deployment chart for the complete full-stack application including observability stack.

## What's Included

### Core Services
- **Nginx** - Reverse proxy and web server
- **PHP FPM** - Symfony backend application
- **MySQL** - Database
- **Redis** - Cache and session storage
- **RabbitMQ** - Message broker for async jobs
- **Worker** - Background job processor

### Observability
- **LGTM Stack** (all-in-one Grafana + Loki + Tempo + Prometheus + OpenTelemetry Collector)

## Quick Start

```bash
# Install the chart with release name 'app'
helm install app ./helm

# Verify deployment
kubectl get pods
kubectl get ingress

# Access the application via Ingress
# App: http://localhost
# Grafana: http://localhost/grafana (login: admin/admin)
# OTLP HTTP: http://localhost/otel
```

## Configuration

All configuration is in `values.yaml`. Key settings:

### Environment
- `global.environment` - Set to `prod` for production mode (no HMR)
- `app.replicas` - Number of app replicas (default: 1)

### Credentials (Change these in production!)
- `app.secret` - Symfony secret key
- `app.admin.email` / `app.admin.password` - Admin credentials
- `database.env.MYSQL_ROOT_PASSWORD` - MySQL root password
- `database.env.MYSQL_PASSWORD` - MySQL user password
- `rabbitmq.env.RABBITMQ_DEFAULT_PASS` - RabbitMQ password

### Observability
- `observability.grafanaRootUrl` - Grafana root URL (default: "http://localhost/grafana")
- `spa.backendApiUrl` - SPA backend API URL (default: "/api")
- `spa.otelCollectorAddress` - OpenTelemetry collector address (default: "http://localhost/otel")

## ⚠️ Production Considerations

### Database, Cache, and RabbitMQ

**Current Setup (Dev/Test):**
- Uses simple Kubernetes `Pod` objects
- **Data is lost** on pod restart
- Ephemeral storage only

**Production Setup:**
- Should use `StatefulSet` instead of `Pod`
- Must use `PersistentVolume` and `PersistentVolumeClaim`
- Consider managed services:
  - AWS RDS for MySQL
  - AWS ElastiCache for Redis
  - AWS MQ for RabbitMQ

**To migrate to StatefulSet + PV:**

1. **For Database:**
   ```yaml
   kind: StatefulSet
   metadata:
     name: db
   spec:
     serviceName: db
     selector:
       matchLabels:
         app: db
     template:
       # ... pod spec ...
       volumeMounts:
       - name: mysql-data
         mountPath: /var/lib/mysql
     volumeClaimTemplates:
     - metadata:
         name: mysql-data
       spec:
         accessModes: [ "ReadWriteOnce" ]
         resources:
           requests:
             storage: 10Gi
   ```

2. **For Cache (Redis):**
   Similar structure with `mountPath: /data` and appropriate storage size

3. **For RabbitMQ:**
   Similar structure with `mountPath: /var/lib/rabbitmq` and persistent storage

### Image Registry

If using private image registry:
```bash
helm install app ./helm \
  --set global.imageRegistry=your-registry.com
```

### Secrets Management

For production, don't commit passwords to git. Use Kubernetes secrets:

```bash
# Create secret
kubectl create secret generic app-secrets \
  --from-literal=db-password=YOUR_DB_PASSWORD \
  --from-literal=mysql-root-password=YOUR_ROOT_PASSWORD \
  --from-literal=app-secret=YOUR_APP_SECRET

# Reference in values or deployment
```

### Resource Limits

Add resource requests/limits in production:

```yaml
# In values.yaml for each container
resources:
  requests:
    memory: "256Mi"
    cpu: "250m"
  limits:
    memory: "512Mi"
    cpu: "500m"
```

### High Availability

For HA setup:
- Set `app.replicas: 3` or higher
- Set `nginx.replicas: 3` or higher
- Convert stateful services to StatefulSets with multiple replicas
- Use RollingUpdate strategy
- Add Pod Disruption Budgets (PDB)

## Troubleshooting

### Check deployment status
```bash
kubectl get all
kubectl describe pod POD_NAME
kubectl logs POD_NAME
```

### Access services via Ingress
```bash
kubectl get ingress

# Access the app
open http://localhost

# Access Grafana
open http://localhost/grafana

# OpenTelemetry endpoints
# gRPC: Inside cluster at lgtm:4317
# HTTP: http://localhost/otel
```

### Database initialization issues
```bash
# Check init job
kubectl describe job init
kubectl logs job/init
```

## Uninstall

```bash
helm uninstall app
```

## Support

For issues, check:
1. Logs: `kubectl logs POD_NAME`
2. Events: `kubectl describe pod POD_NAME`
3. Services connectivity: `kubectl exec POD_NAME -- nc -zv SERVICE_NAME:PORT`
4. Ingress routing: `kubectl get ingress` and `curl -v http://localhost/`
