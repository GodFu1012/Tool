#!/bin/bash

PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin:/root/bin
export PATH


ftype="$(file "$1")"

case "$ftype" in

"$1: Zip archive"*)
   unzip "$1" ;;
"$1: gzip compressed data"*)
   tar xzvf "$1";;
"$1: bzip2 compressed data"*)
   tar xjvf "$1";;
*) echo "File $1 can not be uncompressed with smartzip";;

esac


