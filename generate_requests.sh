#!/bin/bash

METHOD="POST"
DATA='{"email": "test@example.com", "password": "password123"}'
HEADERS=("Content-Type: application/json")
REQUESTS=10000
CONCURRENT_REQUESTS=10000

ENDPOINTS=(
    "http://localhost/api/login"
)

send_request() {
    local endpoint=$1
    curl -X "$METHOD" "$endpoint" \
        -H "${HEADERS[0]}" \
        -d "$DATA" \
        --silent --output /dev/null --write-out "%{http_code}\n"
}

export -f send_request

send_requests_for_endpoint() {
    local endpoint=$1
    seq "$REQUESTS" | xargs -P "$CONCURRENT_REQUESTS" -I {} bash -c "send_request $endpoint"
}

for endpoint in "${ENDPOINTS[@]}"; do
    echo "Enviando $REQUESTS requisições para: $endpoint"
    send_requests_for_endpoint "$endpoint" &
done

wait

echo "Todas as requisições foram concluídas."
