#!/bin/bash
rm -rf build/
mkdir build/
cp *.php build/
cp -rf lib/ build/lib/
cp -rf controllers/ build/controllers/
cp -rf models/ build/models/
cp -rf www/ build/www/
cp -rf config/ build/config/
cp -rf declarations/ build/declarations/

cp composer.* build/
cp .dockerignore build/

docker build -f Dockerfile.build -t legalthings/legalevents .
