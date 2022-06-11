import sys

if len(sys.argv) < 2:
    print('usage: orb.py experience_file name (name of the file in the experiences folder without the py extension)')
    sys.exit(0)

experience = sys.argv[1]

if experience[-3:] == '.py' or experience[-3:] == '.PY':
    experience = experience[:-3]

print(f'load experience {experience}')

def download_from_of(url):
    print(f'downloading from OF {url}')

mod = __import__(f'experiences.{experience}', fromlist=[''])
mod.main(download_from_of)
