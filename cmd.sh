#!/bin/bash

wrapper=../cmd.sh

repo=$(dirname -- "$(readlink -f -- "$0")")
project=/host/${repo##*/}

cmd="$1"; shift



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
  exec "$wrapper" bash "cd $project && exec symfony server:log"; # $ tail -f ~/.symfony5/log/*.log ~/.symfony5/log/*/*.log

elif [ "$cmd" == 'dump' ]; then
  exec "$wrapper" bash "cd $project && exec bin/console server:dump"



elif [ "$cmd" == 'watch' ]; then
  exec "$wrapper" bash "cd $project && exec npm run watch"

elif [ "$cmd" == 'dev-server' ]; then
  exec "$wrapper" bash "cd $project && exec npm run dev-server"

elif [ "$cmd" == 'dev-live-php' ]; then
  exec "$wrapper" bash "cd $project && exec npm run dev-server -- --live-reload"

elif [ "$cmd" == 'dev-live' ]; then
  exec "$wrapper" bash "cd $project && staticWatch=false exec npm run dev-server -- --live-reload"



elif [ "$cmd" == 'phpunit' ]; then
  # php -d memory_limit=-1 bin/phpunit
  "$wrapper" bash "cd $project && bin/phpunit $*"; # --colors=never | less -S

elif [ "$cmd" == 'phpunit-dump' ]; then
  "$wrapper" bash "cd $project && VAR_DUMPER_FORMAT=server bin/phpunit $*"; # --colors=never | less -S

elif [ "$cmd" == 'lint' ]; then
  #"$wrapper" bash "cd $project && bin/console lint:container"
  "$wrapper" bash "cd $project && bin/console lint:container && bin/console lint:twig templates/"



elif [ "$cmd" == 'rabbitmq' ]; then
  "$wrapper" bash "/host/rabbitmq_server-3.12.12/sbin/rabbitmq-server -detached"

elif [ "$cmd" == 'liveTrades:serve' ]; then
  exec "$wrapper" bash "cd $project && exec bin/console liveTrades:serve"

elif [ "$cmd" == 'liveTrades:client' ]; then
  exec "$wrapper" bash "cd $project && exec bin/console liveTrades:client"



elif [ "$cmd" == 'log-dev' ]; then
  exec tail -f "$repo/var/log/dev.log"

elif [ "$cmd" == 'browser' ]; then
  exec x-www-browser &

elif [ "$cmd" == 'bash' ]; then
  exec "$wrapper" bash $*



elif [ "$cmd" == 'readme' ]; then
  cat << EOF
Helper script for symfony and podman (docker) container.
EOF

else
  cat << EOF
Usage: cmd.sh <serve | serve-debug | stop | log-php | dump>
       cmd.sh <watch | dev-server | dev-live-php | dev-live>
       cmd.sh <phpunit $* | phpunit-dump $* | lint>
       cmd.sh <rabbitmq | liveTrades-serve | liveTrades-client>
       cmd.sh <log-dev | browser | bash $* >
EOF

fi
