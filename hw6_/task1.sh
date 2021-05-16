#!/bin/bash
a=$1
sed '/^$/d' $a | sed 's/[a-z]/\U&/g' > ./output.txt
cat ./output.txt
