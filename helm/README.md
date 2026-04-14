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
# Install the chart with release name 'myapp'
helm install myapp ./helm \
  -n myapp-ns \
  --create-namespace

# Or use different release name
helm install production ./helm \
  -n prod \
  --create-namespace

# Verify deployment
kubectl get pods -n myapp-ns

# Access the application
# Web: http://localhost:30080
# Grafana: http://localhost:30300 (login: admin/admin)
# OTLP gRPC: localhost:30317
# OTLP HTTP: http://localhost:30318
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
- `observability.nodePorts.grafana` - Port to access Grafana (default: 30300)
- `observability.nodePorts.otelGrpc` - Port to access OTLP gRPC (default: 30317)
- `observability.nodePorts.otelHttp` - Port to access OTLP HTTP (default: 30318)

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
helm install fullstack ./helm/fullstack \
  --set global.imageRegistry=your-registry.com \
  -n fullstack
```

### Secrets Management

For production, don't commit passwords to git. Use Kubernetes secrets:

```bash
# Create secret
kubectl create secret generic fullstack-secrets \
  --from-literal=db-password=YOUR_DB_PASSWORD \
  --from-literal=mysql-root-password=YOUR_ROOT_PASSWORD \
  --from-literal=app-secret=YOUR_APP_SECRET \
  -n fullstack

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
kubectl get all -n fullstack
kubectl describe pod POD_NAME -n fullstack
kubectl logs POD_NAME -n fullstack
```

### Access services
```bash
kubectl get svc -n fullstack

# Access the app
open http://localhost:30080

# Access Grafana
open http://localhost:30300

# OTLP endpoints
# gRPC: localhost:30317
open http://localhost:30318
```

### Database initialization issues
```bash
# Check init job
kubectl describe job fullstack-init -n fullstack
kubectl logs job/fullstack-init -n fullstack
```

## Uninstall

```bash
helm uninstall fullstack -n fullstack
```

## Support

For issues, check:
1. Logs: `kubectl logs -n fullstack`
2. Events: `kubectl describe pod POD_NAME -n fullstack`
3. Services connectivity: `kubectl exec POD_NAME -n fullstack -- nc -zv SERVICE_NAME:PORT`
