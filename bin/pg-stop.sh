#!/bin/bash
PGDATA="$(dirname "$0")/../docker/pgdata"
PGPORT=5433
PGSOCKET=/run/user/1000

if ! pg_isready -q -p $PGPORT -h $PGSOCKET 2>/dev/null; then
    echo "PostgreSQL is not running."
    exit 0
fi

pg_ctl -D "$PGDATA" stop
echo "PostgreSQL stopped."
