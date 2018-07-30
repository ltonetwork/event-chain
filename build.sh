#!/bin/bash
rm -rf build/
mkdir build/
cp *.php composer.* .dockerignore build/
cp -rf lib controllers models www config declarations build/

#docker build -f Dockerfile.build -t legalthings/legalevents .
