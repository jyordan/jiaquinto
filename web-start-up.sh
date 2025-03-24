#!/bin/bash

echo -n " | system up: $(date)" >> ./public/check-alive.txt

# Start Supervisor in the foreground
exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf
