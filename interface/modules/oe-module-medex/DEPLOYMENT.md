# MedEx Module Deployment Guide

## Configuration Options  

### Default Configuration (All Installations)

The module defaults to the **public SaaS API**:
```
https://api.hipaabank.net
```

This works for:
- Bare metal installations
- VMs (AWS EC2, Azure VMs, etc.)
- Docker containers
- Single-server deployments  
- Different Kubernetes clusters

**No configuration needed** - registration will work out of the box.

---

## Performance Optimization for Kubernetes

### Same-Cluster Deployments

If **both** OpenEMR and MedEx API run in the **same Kubernetes cluster**, use internal DNS for better performance and to avoid ingress PROXY protocol issues.

#### Why Internal DNS?

**Problem:** External ingress configured with PROXY protocol rejects pod-to-pod connections
**Solution:** Use cluster-internal DNS to bypass ingress entirely

#### Configuration Steps

1. **Admin → Globals → MedEx**
2. Add/update `medex_base_url`:
   ```
   http://medex-api.medex.svc.cluster.local/cart/upload
   ```

Or via SQL:
```sql
INSERT INTO globals (gl_name, gl_index, gl_value) 
VALUES ('medex_base_url', 0, 'http://medex-api.medex.svc.cluster.local/cart/upload')
ON DUPLICATE KEY UPDATE gl_value='http://medex-api.medex.svc.cluster.local/cart/upload';
```

**Note:** Adjust namespace/service names if different:
- Namespace: `medex` (change if your MedEx API is in a different namespace)
- Service: `medex-api` (must match your Service name)

---

## Deployment Environment Matrix

| Environment | MedEx API URL | Notes |
|-------------|---------------|-------|
| **Production SaaS** | `https://api.hipaabank.net` | Default - works everywhere |
| **K8s Same Cluster** | `http://medex-api.medex.svc.cluster.local/cart/upload` | Best performance, avoids ingress |
| **Docker Compose** | `http://medex-api/cart/upload` | Service name from docker-compose.yml |
| **AWS EKS (different cluster)** | `https://api.hipaabank.net` | Use public URL |
| **Development (localhost)** | `http://localhost:8081/cart/upload` | For local testing |

---

## Troubleshooting

### "Connection reset by peer" Error

**Symptom:**  
```
Registration error: cURL error 35: Recv failure: Connection reset by peer
```

**Cause:** OpenEMR pod trying to connect via external HTTPS, hitting ingress PROXY protocol rejection

**Fix:** Set `medex_base_url` to internal DNS (see above)

### "No route to host" Error  

**Cause:** Internal DNS not resolvable (pods in different clusters or namespaces)

**Fix:** Use public HTTPS URL instead

---

## Verifying Configuration

Test from OpenEMR pod:
```bash
# If using internal DNS:
kubectl exec -n openemr <pod-name> -- curl http://medex-api.medex.svc.cluster.local/cart/upload/index.php?route=api/oemr/ping

# If using public URL:
kubectl exec -n openemr <pod-name> -- curl https://api.hipaabank.net/cart/upload/index.php?route=api/oemr/ping
```

Both should return:
```json
{"success":true,"message":"MedEx API is operational"}
```

---

## Security Notes

- **Internal DNS (HTTP):** Safe within K8s - encrypted at network layer by CNI
- **Public URL (HTTPS):** TLS encryption for internet traffic  
- **Never expose internal services** via LoadBalancer - use ingress for external access

---

## For Module Distributors

Default configuration uses public HTTPS for maximum compatibility. Document the K8s optimization in your deployment guides but **do not hardcode** internal DNS in the module.
