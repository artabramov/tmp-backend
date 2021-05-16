#!/bin/bash
for year in {2010..2017}; do for month in {01..12}; do if [ ! -d ./$year ]; then mkdir ./$year; fi; if [ ! -d ./$year/$month ]; then mkdir ./$year/$month; fi; for i in {001..003}; do echo "filename "$i >> ./$year/$month/$i".txt"; done done done
