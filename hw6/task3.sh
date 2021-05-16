#!/bin/bash
 ls -lai /etc | grep -v '^total' | awk '{ print $2 }' | sort | uniq

