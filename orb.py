import sys
import os
import time
import hashlib
import zipfile
import requests
import re
import importlib.util
from pathlib import Path
import json

def load_experience_module(experience_module_name):
    module_name = experience_module_name[:-3]
    spec = importlib.util.spec_from_file_location(module_name, f'experiences/{experience_module_name}')
    mod = importlib.util.module_from_spec(spec)
    sys.modules[module_name] = mod
    spec.loader.exec_module(mod)
    return mod

def is_file_cached(file_name):
    return os.path.exists(f'orb_cache/{file_name}')

def download_from_of(url):
    print(f'downloading from OF {url}')

def download_zip(url, file_name=None):
    if file_name is None:
        file_name = url.split('/')[-1]

    if is_file_cached(file_name):
        print(f'{file_name} is already in cache')
        return

    print(f'downloading {url}')
    r = requests.get(url)
    if 'Content-Disposition' in r.headers:
        zip_file_name = r.headers['Content-Disposition'].split('filename=')[1]
    else:
        zip_file_name = file_name

    # lunar.industries
    with open(f'orb_cache/{zip_file_name}', "wb") as f:
        f.write(r.content)
    
def should_generate_hash(path):
    # check if path is a dir or file
    if path.is_dir():
        return False
    # check if path is a .py file
    if path.suffix == '.py':
        return False
    # skip orb.exe
    if path.name == 'orb.exe':
        return False
    # skip orb
    if path.name == 'orb':
        return False
    if path.name == 'Orbiter2016.json':
        return False
    return True

def generate_file_hash(full_path):
    with open(full_path, 'rb') as f:
        content = f.read()
        # generate md5 hash for content string
        return hashlib.md5(content).hexdigest()

def generate_orbiter_hash(output_hash_file):
    files = {}
    for path in Path('./').rglob('*.*'):
        try:
            if not should_generate_hash(path):
                continue
            # get full path name
            full_path = str(path)
            files[full_path] = generate_file_hash(full_path)
            print(full_path)
        except Exception as e:
            print(e)
    with open(output_hash_file, 'w') as f:
        json.dump(files, f)

def verify_orbiter_hash(hash_file):
    with open(hash_file, 'r') as f:
        files = json.load(f)
    for path in Path('./').rglob('*.*'):
        try:
            if not should_generate_hash(path):
                continue
            # get full path name
            full_path = str(path)
            if full_path not in files:
                print(f'{full_path} is not in the hash file')
                continue
            if files[full_path] != generate_file_hash(full_path):
                print(f'{full_path} has changed')
        except Exception as e:
            print(e)
    # sleep 
    time.sleep(3)
    

def main():
    #generate_orbiter_hash('Orbiter2016.json')
    verify_orbiter_hash('Orbiter2016.json')
    time.sleep(20)
    sys.exit(0)
    experiences = {}
    counter = 0
    if not os.path.exists('orb_cache'):
        os.mkdir('orb_cache')

    for experience in os.listdir('experiences'):
        if experience.lower()[-3:] == '.py':
            experiences[experience] = mod = load_experience_module(experience)
            print(f'{counter+1}: {mod.get_name()}')        
            counter += 1


    user_input = input('\nEnter the number of the experience you want to use: ')

    try:
        mod_index = int(user_input) - 1
    except:
        print('Invalid input')
        sys.exit(1)

    mod = experiences[list(experiences.keys())[mod_index]]

    if mod.requires_fresh_install():
        print('This experience requires a fresh install')
        test = input('continue? (y/n): ')
        if test.lower() != 'y':
            print('ok, bye!')
            time.sleep(3)
            sys.exit(1)
    mod.main(download_from_of, download_zip)
    print('ok, bye!')
    time.sleep(3)

main()