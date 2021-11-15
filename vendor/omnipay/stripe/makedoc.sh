#!/bin/sh

#
# Smart little documentation generator.
# GPL/LGPL
# (c) Del 2015 http://www.babel.com.au/
#

APPNAME='Omnipay Stripe Gateway Module'
CMDFILE=apigen.cmd.$$
DESTDIR=./documents

#
# Find apigen, either in the path or as a local phar file
#
if [ -f apigen.phar ]; then
    APIGEN="php apigen.phar"

else
    APIGEN=`which apigen`
    if [ ! -f "$APIGEN" ]; then
        
        # Search for phpdoc if apigen is not found.
        if [ -f phpDocumentor.phar ]; then
            PHPDOC="php phpDocumentor.phar"
        
        else
            PHPDOC=`which phpdoc`
            if [ ! -f "$PHPDOC" ]; then
                echo "Neither apigen nor phpdoc is installed in the path or locally, please install one of them"
                echo "see http://www.apigen.org/ or http://www.phpdoc.org/"
                exit 1
            fi
        fi
    fi
fi

#
# As of version 4 of apigen need to use the generate subcommand
#
if [ ! -z "$APIGEN" ]; then
    APIGEN="$APIGEN generate"
fi

#
# Without any arguments this builds the entire system documentation,
# making the cache file first if required.
#
if [ -z "$1" ]; then
    #
    # Check to see that the cache has been made.
    #
    if [ ! -f dirlist.cache ]; then
        echo "Making dirlist.cache file"
        $0 makecache
    fi

    #
    # Build the apigen/phpdoc command in a file.
    #
    if [ ! -z "$APIGEN" ]; then
        echo "$APIGEN --php --tree --title '$APPNAME API Documentation' --destination $DESTDIR/main \\" > $CMDFILE
        cat dirlist.cache | while read dir; do
            echo "--source $dir \\" >> $CMDFILE
        done
        echo "" >> $CMDFILE
    
    elif [ ! -z "$PHPDOC" ]; then
        echo "$PHPDOC --sourcecode --title '$APPNAME API Documentation' --target $DESTDIR/main --directory \\" > $CMDFILE
        cat dirlist.cache | while read dir; do
            echo "${dir},\\" >> $CMDFILE
        done
        echo "" >> $CMDFILE
    
    else
        "Neither apigen nor phpdoc are found, how did I get here?"
        exit 1
    fi

    #
    # Run the apigen command
    #
    rm -rf $DESTDIR/main
    mkdir -p $DESTDIR/main
    . ./$CMDFILE
    
    /bin/rm -f ./$CMDFILE

#
# The "makecache" argument causes the script to just make the cache file
#
elif [ "$1" = "makecache" ]; then
    echo "Find application source directories"
    find src -name \*.php -print | \
        (
            while read file; do
                grep -q 'class' $file && dirname $file
            done
        ) | sort -u | \
        grep -v -E 'config|docs|migrations|phpunit|test|Test|views|web' > dirlist.app

    echo "Find vendor source directories"
    find vendor -name \*.php -print | \
        (
            while read file; do
                grep -q 'class' $file && dirname $file
            done
        ) | sort -u | \
        grep -v -E 'config|docs|migrations|phpunit|codesniffer|test|Test|views' > dirlist.vendor
  
    #
    # Filter out any vendor directories for which apigen fails
    #
    echo "Filter source directories"
    mkdir -p $DESTDIR/tmp
    cat dirlist.app dirlist.vendor | while read dir; do
        if [ ! -z "$APIGEN" ]; then
            $APIGEN --quiet --title "Test please ignore" \
                --source $dir \
                --destination $DESTDIR/tmp && (
                    echo "Including $dir"
                    echo $dir >> dirlist.cache
                ) || (
                    echo "Excluding $dir"
                )
        
        elif [ ! -z "$PHPDOC" ]; then
            $PHPDOC --quiet --title "Test please ignore" \
                --directory $dir \
                --target $DESTDIR/tmp && (
                    echo "Including $dir"
                    echo $dir >> dirlist.cache
                ) || (
                    echo "Excluding $dir"
                )

        fi
    done
    echo "Documentation cache dirlist.cache built OK"
    
    #
    # Clean up
    #
    /bin/rm -rf $DESTDIR/tmp

fi
