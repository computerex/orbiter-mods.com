def main(download_from_of, download_zip, install_zip, enable_modules):
    download_zip('https://www.alteaaerospace.com/ccount/click.php?id=3', 'XR2 Ravenstar.zip')
    install_zip('XR2 Ravenstar.zip')
    download_zip('https://www.alteaaerospace.com/ccount/click.php?id=55', 'XRSound.zip')
    install_zip('XRSound.zip')
    print('downloading from OF')
    download_from_of('https://www.orbiter-forum.com/resources/soundbridge.204/download', 'SoundBridge1.1.zip')
    install_zip('SoundBridge1.1.zip')
    download_zip('http://users.kymp.net/~p501474a/D3D9Client/D3D9ClientR4.25-forOrbiter2016(r1446).zip', 'D3D9ClientR4.25-forOrbiter2016(r1446).zip')
    install_zip('D3D9ClientR4.25-forOrbiter2016(r1446).zip')
    
    enable_modules(['OrbiterSound', 'XRSound', 'ScnEditor', 'transx', 'Rcontrol', 'ExtMFD'])

def requires_fresh_install():
    return False

if __name__ == '__main__':
    main()