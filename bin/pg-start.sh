#!/bin/bash
PGDATA="$(dirname "$0")/../docker/pgdata"
PGPORT=5433
PGSOCKET=/run/user/1000

if pg_isready -q -p $PGPORT -h $PGSOCKET 2>/dev/null; then
    echo "PostgreSQL is already running on port $PGPORT."
    exit 0
fi

pg_ctl -D "$PGDATA" -l "$PGDATA/logfile" start -o "-p $PGPORT -k $PGSOCKET"
echo "PostgreSQL started on port $PGPORT (socket: $PGSOCKET)"
