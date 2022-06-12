def main(download_from_ohm, download_zip):
    download_zip('https://www.alteaaerospace.com/downloads/releases/XR2Ravenstar-1.10.zip')

def get_name():
    return 'XR2 Ravenstar'

def requires_fresh_install():
    return True

if __name__ == '__main__':
    main()