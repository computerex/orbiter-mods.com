import sys
import os
import time


import importlib.util

def load_experience_module(experience_module_name):
    module_name = experience_module_name[:-3]
    spec = importlib.util.spec_from_file_location(module_name, f'experiences/{experience_module_name}')
    foo = importlib.util.module_from_spec(spec)
    sys.modules[module_name] = foo
    spec.loader.exec_module(foo)
    return foo


experiences = {}
counter = 0
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
    
sys.exit(0)

def download_from_of(url):
    print(f'downloading from OF {url}')


mod.main(download_from_of)
