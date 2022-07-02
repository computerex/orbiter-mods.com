import sys
import os
import time
import hashlib
from unittest import skip
import zipfile
import shutil
import requests
import re
import importlib.util
from pathlib import Path
import json
import webbrowser
import tkinter as tk
from tkinter import filedialog
import subprocess
import ctypes
import traceback
from clint.textui import progress


DEBUG = 0

def is_user_admin():
    try:
        is_admin = os.getuid() == 0
    except AttributeError:
        is_admin = ctypes.windll.shell32.IsUserAnAdmin() != 0
    
    return is_admin

def open_select_file_dialog():
    root = tk.Tk()
    root.withdraw()

    file_path = filedialog.askopenfilename()
    return file_path

def load_experience_module(experience_module_path):
    module_name = experience_module_path[:-3]
    spec = importlib.util.spec_from_file_location(module_name, experience_module_path)
    mod = importlib.util.module_from_spec(spec)
    sys.modules[module_name] = mod
    spec.loader.exec_module(mod)
    return mod

def is_file_cached(file_name):
    return os.path.exists(f'orb_cache/{file_name}')

def wait_for_file_download(file_path):
    # wait for the file_path file to be created
    print('waiting for file to be created')
    while not os.path.exists(file_path):
        time.sleep(1)

    # check the file's last modified timestamp
    last_modified = os.path.getmtime(file_path)
    # return file_path when file's last modified timestamp has remained unchanged
    while True:
        print(f'sleeping for 5 seconds, checking if {file_path} is fully downloaded')
        time.sleep(5)
        cur_time = os.path.getmtime(file_path)
        if cur_time == last_modified:
            return file_path
        last_modified = cur_time
    return file_path

def locate_file(expected_zip_name):
    download_folder_path = get_download_folder_path()
    file_path = os.path.join(download_folder_path, expected_zip_name)
    print(f'trying to find download in download folder: {file_path}')
    if os.path.exists(file_path):
        return file_path
    print('trying to find crdownload file in download folder')
    if os.path.exists(f'{file_path}.crdownload'):
        print(f'found {file_path}.crdownload')
        return file_path
    print('unable to automatically locate file. please wait for the download to finish, and then press enter to continue')
    input()
    # ask user to specify the download file location by using the open file dialog
    return open_select_file_dialog()

# get download folder path for the user
def get_download_folder_path():
    if os.name == 'nt':
        return os.path.join(os.environ['USERPROFILE'], 'Downloads')
    else:
        return os.path.join(os.environ['HOME'], 'Downloads')
  
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
    if path.name == 'Orbiter.cfg':
        return False
    if path.name == 'Orbiter_NG.cfg':
        return False

    full_path = str(path)
    # skip orb_cache
    if full_path.startswith('./orb_cache') or full_path.startswith('orb_cache'):
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

def download_orbiter_2016_if_needed(orb):
    # check if orbiter 2016 is already downloaded and unzipped in orb_cache
    if os.path.exists('./orb_cache/Orbiter2016'):
        pass
    else:
        # check if Orbiter2016.zip is in orb_cache
        if is_file_cached('Orbiter2016.zip'):
            print('Orbiter2016.zip is already in cache')
        else:
            orbiter_url = 'http://alteaaerospace.com/ccount/click.php?id=47'
            if DEBUG == 1:
                orbiter_url = 'http://localhost:8000/Orbiter2016.zip'

            # ask user if they want to continue to download Orbiter2016
            print('Orbiter2016 is not in cache. would you like to download it?')
            if input('(y/n) ') != 'y':
                print('aborting orbiter2016 download')
                return        
            orb.download_zip(orbiter_url, 'Orbiter2016.zip')
        # unzip Orbiter2016.zip to orb_cache/Orbiter2016
        print('unzipping Orbiter2016.zip')
        with zipfile.ZipFile(f'orb_cache/Orbiter2016.zip', 'r') as zip_ref:
            zip_ref.extractall('./orb_cache/Orbiter2016')
    # check if orbiter.exe is in the current folder
    if not os.path.exists('orbiter.exe'):
        # ask user if they'd like to install a fresh copy of orbiter 2016
        print('orbiter.exe is not in the current folder. would you like to install a fresh copy of orbiter 2016?')
        if input('(y/n): ') == 'y':
            # copy all files from orb_cache/Orbiter2016 to current folder keeping directory structure in tact
            print('copying files from Orbiter2016 to current folder')
            shutil.copytree(os.path.join('orb_cache', 'Orbiter2016'), '.', dirs_exist_ok=True)

def reset_orbiter():
    # ask user to confirm as doing this action will destroy the current orbiter install
    test = input('Are you sure you want to reset the orbiter install? (y/n): ')
    if test.lower() != 'y':
        print('aborting orbiter install reset')
        time.sleep(3)
        return
    # verify current orbiter install hash using ./Orbiter2016.json as reference hash_file
    files_to_revert = verify_orbiter_hash('./Orbiter2016.json')
    download_orbiter_2016_if_needed()
    for file_to_revert in files_to_revert:
        print(f'reverting {file_to_revert}')
        os.remove(file_to_revert)
        # copy removed file from orb_cache/Orbiter2016 to ./
        shutil.copy(f'orb_cache/Orbiter2016/{file_to_revert}', file_to_revert)

class Orb:
    def __init__(self, scn_dir=None):
        self.scn_dir = scn_dir

    def install_exe(self, file):
        # check if file is in current folder
        if not os.path.exists(file):
            # check if file is in orb_cache
            if not is_file_cached(file):
                print(f'{file} is not in cache or current folder - have you downloaded it?')
                return
        else:
            # move file to orb_cache
            print(f'{file} is in current folder. moving to cache')
            shutil.move(file, f'orb_cache/{file}')
        # run the downloaded exe at orb_cache/file with subprocess run
        print(f'running {file}')
        subprocess.run(f'orb_cache/{file}')

    def install_zip(self, file, install_subdir=None):
        output_dir = './'
        if install_subdir:
            output_dir = os.path.join('orb_cache', install_subdir)

        scn_files = []
        with zipfile.ZipFile(f'orb_cache/{file}', 'r') as zip_ref:
            zip_ref.extractall(output_dir)
            # get list of all files in zip
            files = zip_ref.namelist()
            # find all files ending with .scn file extension
            scn_files = [
                file[file.find('Scenarios/')+10:] for file in files if file.lower().endswith('.scn') and file.find('Scenarios/') != -1
            ]
        
        if install_subdir:
            # copy all files from orb_cache/install_subdir to current folder keeping directory structure in tact
            print('copying files from ' + os.path.join('orb_cache', install_subdir, install_subdir) + ' to current folder')
            shutil.copytree(os.path.join('orb_cache', install_subdir, install_subdir), '.', dirs_exist_ok=True)

        # create new directory in Scenarios/ folder named self.scn_dir
        if not os.path.exists(os.path.join('.', 'Scenarios', self.scn_dir)):
            print(f'creating new directory in Scenarios/ folder named {self.scn_dir}')
            try:
                os.makedirs(os.path.join('Scenarios', self.scn_dir))
            except Exception as e:
                pass
        # copy all scn_files to Scenarios/self.scn_dir
        if len(scn_files) > 0:
            print(f'copying all .scn files to Scenarios/{self.scn_dir}')
        for scn_file in scn_files:
            print(f'copying {scn_file} to {os.path.join("Scenarios", self.scn_dir)}')
            shutil.copy(os.path.join('.', 'Scenarios', scn_file), os.path.join('.', 'Scenarios', self.scn_dir))

    def install_rar(self, file, install_subdir=None):
        output_dir = os.path.join('orb_cache', os.path.splitext(file)[0])

        # extract the rar in orb_cache. We need to do this for automatic scenario copying
        subprocess.run(f'{os.path.join("orb_cache", "unarr.exe")} {os.path.join("orb_cache", file)} {output_dir}')

        if install_subdir:
            output_dir = os.path.join(output_dir, install_subdir)
        
        # recursively copy all files from output_dir to current folder keeping directory structure in tact
        print('copying files from ' + output_dir + ' to current folder')
        shutil.copytree(output_dir, '.', dirs_exist_ok=True)

        scn_files = []
        # recursively get a list of all .scn files in output_dir
        for root, dirs, files in os.walk(output_dir):
            for file in files:
                if file.lower().endswith('.scn'):
                    scn_files.append(os.path.join(root, file))
        
        for scn in scn_files:
            print(f'copying {scn} to Scenarios/{self.scn_dir}')
            shutil.copy(scn, os.path.join('Scenarios', self.scn_dir))

    def edit_cfg_file_remove_line(self, cfg, new_line):
        # check if config file exists
        if not os.path.exists(cfg):
            print(f'{cfg} does not exist')
            pass
        else:
            try:
                lines = []
                with open(cfg, 'r') as f:
                    for line in f:
                       if line.strip() != new_line.strip():
                           lines.append(line)
                with open(cfg, 'w') as f:
                    f.writelines(lines)
            except Exception as e:
                print(e)
                return

    def edit_cfg_file_add_line(self, cfg, new_line):
        # check if config file exists
        if not os.path.exists(cfg):
            print(f'{cfg} does not exist')
            # create config file and add the start/end tags
            with open(cfg, 'w') as f:
                f.write(f'{new_line}\n')
        else:
            try:
                lines = []
                exists = False
                print(f'opening {cfg}')
                with open(cfg, 'r') as f:
                    for line in f:
                        if line.strip() == new_line.strip():
                            exists = True
                            lines = []
                            break
                        lines.append(line)
                if not exists:
                    lines.append(new_line + '\n')
                    
                    with open(cfg, 'w') as f:
                        for line in lines:
                            f.write(line)
            except Exception as e:
                print(e)
                return

    def edit_cfg_file_section(self, cfg, start_tag, end_tag, new_lines):
        current_list = []
        output_cfg_lines = []
        
        # check if config file exists
        if not os.path.exists(cfg):
            print(f'{cfg} does not exist')
            # create config file and add the start/end tags
            with open(cfg, 'w') as f:
                f.write(f'{start_tag}\n')
                for line in new_lines:
                    f.write(f'{line}\n')
                f.write(f'{end_tag}\n')
        else:
            try:
                with open(cfg, 'r') as f:
                    collect_lines = False
                    for line in f:
                        # check if line starts with start_tag
                        if line.startswith(start_tag):
                            collect_lines = True
                            continue
                        # check if line starts with end_tag
                        if line.startswith(end_tag):
                            collect_lines = False
                            continue
                        if collect_lines:
                            current_list.append(line.strip())
                            continue
                        output_cfg_lines.append(line)
            except Exception as e:
                print(e)
                return

            for line in new_lines:
                # check if line is already in the file
                if line in current_list:
                    continue
                current_list.append(line)
        
            # re-write cfg
            with open(cfg, 'w') as f:
                for line in output_cfg_lines:
                    f.write(line)
                f.write(f'{start_tag}\n')
                for line in current_list:
                    f.write(f'  {line}\n')
                f.write(f'{end_tag}\n')


    def enable_modules(self, module_names, enable_for_orbiter_ng=False):
        config_file = 'Orbiter.cfg'
        if enable_for_orbiter_ng:
            config_file = 'Orbiter_NG.cfg'
        self.edit_cfg_file_section(config_file, 'ACTIVE_MODULES', 'END_MODULES', module_names)

    def download_zip(self, url, file_name=None, skip_cache=False):
        if file_name is None:
            file_name = url.split('/')[-1]

        if not skip_cache and is_file_cached(file_name):
            print(f'{file_name} is already in cache')
            return

        print(f'downloading {url}')
        r = requests.get(url, allow_redirects=True, stream=True)
        if 'Content-Disposition' in r.headers:
            zip_file_name = r.headers['Content-Disposition'].split('filename=')[1]
        else:
            zip_file_name = file_name

        with open(f'orb_cache/{zip_file_name}', 'wb') as f:
            total_length = int(r.headers.get('content-length'))
            for chunk in progress.bar(r.iter_content(chunk_size=1024), expected_size=(total_length/1024) + 1): 
                if chunk:
                    f.write(chunk)
                    f.flush()

    def download_from_of(self, url, expected_zip_name):
        if is_file_cached(expected_zip_name):
            print(f'{expected_zip_name} is already in cache')
            return

        print(f'opening browser to download from OF: {url}')
        webbrowser.open(url)

        # wait for the download to start
        print('waiting for download to start')
        time.sleep(5)
        file_path = locate_file(expected_zip_name)
        if file_path == '':
            print('unable to locate file')
            return
        wait_for_file_download(file_path)
        # copy file at file_path to orb_cache
        shutil.copy(file_path, f'orb_cache/{expected_zip_name}')
    
    def set_scn_dir(self, scn_dir):
        self.scn_dir = scn_dir
    
    def set_experience_list(self, experiences):
        self.experience_list = experiences

    def install_orbiter_mods_experience(self, experience_id):
        xp = [x for x in self.experience_list if 'id' in x and x['id'] == experience_id]
        if len(xp) == 0:
            print(f'experience {experience_id} not found')
            return
        execute_script(self.experience_list, xp[0])

# fetch experiences from https://orbiter-mods.com/fetch_experiences
def fetch_experiences():
    host = 'https://orbiter-mods.com'
    if DEBUG == 1:
        host = 'http://localhost:8000'
    print(f'fetching {host}/fetch_experiences')
    r = requests.get(f'{host}/fetch_experiences')
    return r.json()

def execute_script(all_experiences, experience):
    experience_script_url = experience['experience_script']
    experience_script_file_name = experience_script_url.split('/')[-1]
    experience_name = experience['name']
    orb = Orb()
    orb.set_scn_dir(experience_script_file_name)
    orb.set_experience_list(all_experiences)

    # fetch experience_script_url and save it in orb_cache
    experience_script_file_name = experience_script_url.split('/')[-1]
    if not is_file_cached(experience_script_file_name):
        orb.download_zip(experience_script_url, experience_script_file_name, skip_cache=True)
    # load experience_script_file_name
    mod = load_experience_module(f'orb_cache/{experience_script_file_name}')

    if mod.requires_fresh_install():
        print('This experience requires a fresh install')
        test = input('continue? (y/n): ')
        if test.lower() != 'y':
            print('ok, bye!')
            time.sleep(3)
            sys.exit(1)
        print('reseting orbiter')
        reset_orbiter()
    try:        
        mod.main(orb)
    except Exception as e:
        print(f'Please contact the author of this experience script: {str(e)}')
        traceback.print_exc()

def main():
    global DEBUG

    # # if launched as admin or root, terminate
    # if is_user_admin():
    #     print('Running the program in admin mode is not supported for security reasons')
    #     return

    if '--debug' in sys.argv:
        DEBUG = 1
        print('turning on debug mode')
    
    if not os.path.exists('orb_cache'):
        # set current working dir to parent directory of this file
        print('moving up dir')
        os.chdir("..")
        print(os.getcwd())

    orb = Orb()

    download_orbiter_2016_if_needed(orb)

    experiences = fetch_experiences()

    # find all *.py files in orb_cache dir
    if '--enable-unknown-source' in sys.argv:
        print('enabling unknown source')
        experience_files  = [f for f in os.listdir('orb_cache') if f.endswith('.py')]
        for experience_file in experience_files:
            experiences.append({
                'name': f'{experience_file} (orb_cache)',
                'description': f'unknown experience script orb_cache/{experience_file} run with care',
                'external_links': [],
                'experience_script': f'orb_cache/{experience_file}'
            })

    for inx, experience in enumerate(experiences):
        print(f'{inx+1}: {experience["name"]}')
        print(f'    {experience["description"]}')
        for link in experience["external_links"]:
            print(f'    {link}')

    user_input = input('\nEnter the number of the experience you want to use: ')

    try:
        mod_index = int(user_input) - 1
    except:
        print('Invalid input')
        sys.exit(1)

    experience = experiences[mod_index]
    execute_script(experiences, experience)

    print('ok, bye!')
    time.sleep(3)

main()