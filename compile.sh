#!/bin/bash
rm -rf build/
mkdir build/
cp *.php composer.* .dockerignore build/
cp -rf lib controllers models www config declarations build/

cd build/

composer install

bin/ioncube_encoder.sh -71 --activate
bin/ioncube_encoder.sh -71 build -o app --keep-comments --replace-target
bin/ioncube_encoder.sh -71 --deactivate
