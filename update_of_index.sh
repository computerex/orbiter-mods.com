#!/bin/sh

cd ../ohm_index
wget https://orbiter-mods.com/mods.json
mv mods.json of.json
python3 main.py
cp of.json ../orbiter-mods.com/web/public
cd ../orbiter-mods.com
git add web/public/of.json
git commit -m "index update"
git pull
git push
