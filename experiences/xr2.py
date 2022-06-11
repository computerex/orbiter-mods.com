def main(download_from_ohm):
    download_from_ohm('http://localhost:8080/experience/xr2')

def get_name():
    return 'XR2 Ravenstar'

def requires_fresh_install():
    return True

if __name__ == '__main__':
    main()