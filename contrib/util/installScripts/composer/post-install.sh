#!/bin/bash
##  Copyright (C) 2016 Scott Wakefield <scott@npclinics.com.au>
##
##  This program is free software; you can redistribute it and/or modify
##  it under the terms of the GNU General Public License as published by
##  the Free Software Foundation; either version 2 of the License, or
##  (at your option) any later version.
##
##  Cleanup the vendor tree.
##  Some libraries distribute admin tools and sample files which should not
##  be published.
##############################################################################
## usage: safe_delete <relpath...>
function safe_delete() {
  for file in "$@" ; do
    if [ -z "$file" ]; then
      echo "Skip: empty file name"
    elif [ -e "$file" ]; then
      rm -rf "$file"
    fi
  done
}

##############################################################################
## Remove example/CLI scripts. 
safe_delete vendor/adodb/adodb-php/tests
safe_delete vendor/smarty/smart/demo

##############################################################################
## Remove .gitignore & .gitattribute files from vendor folder   
find ./vendor/** -name ".git*" -exec rm -rf {} \;
