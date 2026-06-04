#!/bin/bash
export PHP_INI_SCAN_DIR="/etc/php.d:/home/raviensewpal/Desktop/Workspace/plazaos/docker/8.4/ext-override"
export LD_LIBRARY_PATH="/home/raviensewpal/Desktop/Workspace/plazaos/docker/8.4/ext-override:$LD_LIBRARY_PATH"
exec php "$(dirname "$0")/artisan" "$@"
