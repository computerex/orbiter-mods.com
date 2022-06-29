import boto3
import pprint
from botocore.client import Config
import progressbar
import sys
import os
import json

# load account_id/access_key_id/secret_access_key from secrets.json file
with open('secrets.json') as f:
    secrets = json.load(f)
    account_id = secrets['account_id']
    access_key_id = secrets['access_key_id']
    secret_access_key = secrets['secret_access_key']
           
endpoint = f'https://{account_id}.r2.cloudflarestorage.com'

def upload_progress(chunk):
    up_progress.update(up_progress.currval + chunk)
 
cl = boto3.client(
    's3',
    aws_access_key_id=access_key_id,
    aws_secret_access_key=secret_access_key,
    endpoint_url=endpoint,
    config=Config(
        s3={'addressing_style': 'path'},
        retries=dict( max_attempts=3 ),
    ),
)
 
printer = pprint.PrettyPrinter().pprint
 
printer(cl.head_bucket(Bucket='orbiter-mods'))

key = sys.argv[1]
filename = sys.argv[2]

statinfo = os.stat(filename)

up_progress = progressbar.progressbar.ProgressBar(maxval=statinfo.st_size)

up_progress.start()

cl.upload_file(filename, 'orbiter-mods', key, Callback=upload_progress)

up_progress.finish()