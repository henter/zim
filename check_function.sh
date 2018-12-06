#!/bin/bash
arr_functions=()
for func in $(
    find . -name "*.php"|ag -v /vendor|xargs cat|ack function|awk -F"function " '{print $2}'|awk -F"(" '{print $1}'|awk '$1'|ack -v " "|ack -v "__construct|__destruct|__autoload|__toString|{"|awk 'length>3'|ack -v "fire|handle|clone|consume|init|exec|line|error|warn|name\."|uniq
    )
do
    echo "checking $func"
    ag "$func" --ignore-dir="vendor" ./|ag -v function
    OUT=$?
    if [ $OUT -ne 0 ]; then
        arr_functions+=($func)
        echo "not found function: $func"
    fi
done
echo "unneeded functions: ${arr_functions[*]}"

