#!/bin/bash

wrapper=../cmd.sh

repo=$(dirname -- "$(readlink -f -- "$0")")
project=/host/${repo##*/}

cmd="$1"



#PS4='Line ${LINENO}: '
#set -e -o pipefail -x
set -e -o functrace; ERR() { echo "ERR($1) at line $2: $3"; exit "$1"; }; trap 'ERR $? ${LINENO} "$BASH_COMMAND"' ERR

if [ "$cmd" == 'serve' ]; then
  "$wrapper" bash "cd $project && symfony serve -d"; # XDEBUG_MODE=off

elif [ "$cmd" == 'serve-debug' ]; then
  "$wrapper" bash "cd $project && XDEBUG_MODE= symfony serve -d"; # XDEBUG_MODE=develop,debug PHP_IDE_CONFIG='serverName=symfony-react host.containers.internal'

elif [ "$cmd" == 'stop' ]; then
  "$wrapper" bash "cd $project && symfony server:stop"

elif [ "$cmd" == 'log-php' ]; then
  exec "$wrapper" bash "cd $project && symfony server:log"

elif [ "$cmd" == 'dump' ]; then
  exec "$wrapper" bash "cd $project && bin/console server:dump"



elif [ "$cmd" == 'watch' ]; then
  exec "$wrapper" bash "cd $project && npm run watch"

elif [ "$cmd" == 'dev-server' ]; then
  exec "$wrapper" bash "cd $project && npm run dev-server"

elif [ "$cmd" == 'dev-live-php' ]; then
  exec "$wrapper" bash "cd $project && npm run dev-server -- --live-reload"

elif [ "$cmd" == 'dev-live' ]; then
  exec "$wrapper" bash "cd $project && staticWatch=false npm run dev-server -- --live-reload"



elif [ "$cmd" == 'phpunit' ]; then
  exec "$wrapper" bash "cd $project && bin/phpunit"

elif [ "$cmd" == 'lint' ]; then
  exec "$wrapper" bash "cd $project && bin/console lint:container"

elif [ "$cmd" == 'bash' ]; then
  exec "$wrapper" bash



elif [ "$cmd" == 'browser' ]; then
  exec x-www-browser &

elif [ "$cmd" == 'log-dev' ]; then
  exec tail -f "$repo/var/log/dev.log"



elif [ "$cmd" == 'readme' ]; then
  cat << EOF
Helper script for symfony and podman (docker) container.
EOF

else
  cat << EOF
Usage: cmd.sh <serve | serve-debug | stop | log-php | dump>
       cmd.sh <watch | dev-server | dev-live-php | dev-live>
       cmd.sh <phpunit | lint | bash | bash $* >
       cmd.sh <browser | log-dev>
EOF

fi
