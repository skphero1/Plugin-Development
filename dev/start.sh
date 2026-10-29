#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
docker_local() {
  env -u DOCKER_HOST -u DOCKER_CONTEXT -u DOCKER_TLS -u DOCKER_TLS_VERIFY -u DOCKER_CERT_PATH docker --host=unix:///var/run/docker.sock "$@"
}
docker_local compose -p atelier -f dev/compose.yaml up -d db wordpress
ready=0
for attempt in {1..30}; do
  if docker_local compose -p atelier -f dev/compose.yaml exec -T wordpress php /var/www/html/wp-content/plugins/atelier-builder/dev/bootstrap.php; then
    ready=1
    break
  fi
  sleep 2
done
if [ "$ready" != 1 ]; then
  docker_local compose -p atelier -f dev/compose.yaml logs --tail=40
  exit 1
fi
python - <<'PY'
import urllib.request
opener = urllib.request.build_opener(urllib.request.ProxyHandler({}))
with opener.open('http://localhost:8080/', timeout=15) as response:
    assert response.status == 200, 'WordPress did not return HTTP 200'
    assert 'Atelier Development' in response.read().decode(), 'Unexpected development site'
print('WordPress development server ready on loopback port 8080.')
PY
