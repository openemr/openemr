#!/usr/bin/env sh
SRC_DIR="`pwd`"
cd "`dirname "$0"`"
cd "../zendframework/zendframework/bin"
BIN_TARGET="`pwd`/pluginmap_generator.php"
cd "$SRC_DIR"
"$BIN_TARGET" "$@"
