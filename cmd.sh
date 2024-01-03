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
  exec "$wrapper" bash "cd $project && symfony server:log"; # $ tail -f ~/.symfony5/log/*.log ~/.symfony5/log/*/*.log

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
  # php -d memory_limit=-1 bin/phpunit
  exec "$wrapper" bash "cd $project && bin/phpunit $*"; # --colors=never | less -S

elif [ "$cmd" == 'phpunit-dump' ]; then
  exec "$wrapper" bash "cd $project && VAR_DUMPER_FORMAT=server bin/phpunit $*"; # --colors=never | less -S

elif [ "$cmd" == 'lint' ]; then
  #exec "$wrapper" bash "cd $project && bin/console lint:container"
  exec "$wrapper" bash "cd $project && bin/console lint:container && bin/console lint:twig templates/"



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
       cmd.sh <log-dev | browser | bash $* >
EOF

fi
