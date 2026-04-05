#!/bin/bash

TIMESTAMP=$(date "+%Y%m%d_%H%M%S")
OUTPUT="hosts_${TIMESTAMP}.ini"

# File initialization
> "$OUTPUT"

cat <<EOF> "$OUTPUT"
[windows]
EOF

for file in *.csv; do
    # Exception handling when the file does not exist
    [ -e "$file" ] || continue

    # Deleting the shortest match in a prefix search
    ip_address="${file%.csv}"

    # Read the contents of the file (serial number)
    serial_number=$(cat "$file")
    echo "${ip_address} serial=${serial_number}" >> "$OUTPUT"
done

cat <<'EOF'>> "$OUTPUT"
[windows:vars]
ansible_user=administrator
ansible_password='P@ssw0rd'
ansible_port=5986
ansible_connection=winrm
ansible_winrm_scheme=https
ansible_winrm_transport=basic
ansible_winrm_server_cert_validation=ignore
EOF

echo "Processing is complete: $OUTPUT"
