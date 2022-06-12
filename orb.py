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
    r = requests.get(url, allow_redirects=True)
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
    files_to_revert = []
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
                files_to_revert.append(full_path)
        except Exception as e:
            print(e)
    return files_to_revert

def reset_orbiter():
    # ask user to confirm as doing this action will destroy the current orbiter install
    test = input('Are you sure you want to reset the orbiter install? (y/n): ')
    if test.lower() != 'y':
        print('aborting orbiter install reset')
        time.sleep(3)
        return
    # verify current orbiter install hash using ./Orbiter2016.json as reference hash_file
    files_to_revert = verify_orbiter_hash('./Orbiter2016.json')

    
# fetch experiences from https://orbiter-mods.com/fetch_experiences
def fetch_experiences():
    r = requests.get('https://orbiter-mods.com/fetch_experiences')
    return r.json()

def download_orbiter_2016_if_needed():
    # check if orbiter 2016 is already downloaded and unzipped in orb_cache
    if os.path.exists('./orb_cache/Orbiter2016'):
        pass
    else:
        # check if Orbiter2016.zip is in orb_cache
        if is_file_cached('Orbiter2016.zip'):
            print('Orbiter2016.zip is already in cache')
        else:
            download_zip('https://orbiter-mods.com/downloads/Orbiter2016.zip', 'Orbiter2016.zip')
            # unzip Orbiter2016.zip
            with zipfile.ZipFile(f'orb_cache/Orbiter2016.zip', 'r') as zip_ref:
                zip_ref.extractall('./orb_cache/')
    
def main():
    #generate_orbiter_hash('Orbiter2016.json')
    #verify_orbiter_hash('Orbiter2016.json')
    #time.sleep(20)
    #sys.exit(0)
    experiences = fetch_experiences()
    
    if not os.path.exists('orb_cache'):
        os.mkdir('orb_cache')

    for inx, experience in enumerate(experiences):
        print(f'{inx+1}: {experience["name"]}')
        print(f'    {experience["description"]}')
        print(f'    {experience["external_link"]}')

    user_input = input('\nEnter the number of the experience you want to use: ')

    try:
        mod_index = int(user_input) - 1
    except:
        print('Invalid input')
        sys.exit(1)

    experience = experiences[mod_index]
    experience_script_url = experience['experience_script']
    # fetch experience_script_url and save it in orb_cache
    experience_script_file_name = experience_script_url.split('/')[-1]
    if not is_file_cached(experience_script_file_name):
        download_zip(experience_script_url, experience_script_file_name)
    # load experience_script_file_name
    mod = load_experience_module(experience_script_file_name)

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