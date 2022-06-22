def main(get_orb_functions):
    download_from_of, download_zip, install_zip, enable_modules, install_rar, install_exe = get_orb_functions()
    download_zip('https://www.alteaaerospace.com/ccount/click.php?id=3', 'XR2 Ravenstar.zip')
    install_zip('XR2 Ravenstar.zip')
    download_zip('https://www.alteaaerospace.com/ccount/click.php?id=55', 'XRSound.zip')
    install_zip('XRSound.zip')
    print('downloading from OF')
    download_from_of('https://www.orbiter-forum.com/resources/soundbridge.204/download', 'SoundBridge1.1.zip')
    install_zip('SoundBridge1.1.zip')
    download_zip('http://users.kymp.net/~p501474a/D3D9Client/D3D9ClientR4.25-forOrbiter2016(r1446).zip', 'D3D9ClientR4.25-forOrbiter2016(r1446).zip')
    install_zip('D3D9ClientR4.25-forOrbiter2016(r1446).zip')
    download_zip('http://users.kymp.net/~p501474a/D3D9Client/MicroTextures.zip', 'MicroTextures.zip')
    install_zip('MicroTextures.zip')

    download_from_of('https://www.orbiter-forum.com/resources/multistage2015-for-orbiter-2016.398/', 'Multistage2015_forOrbiter2016.zip')
    install_zip('Multistage2015_forOrbiter2016.zip')

    enable_modules(['OrbiterSound', 'XRSound', 'ScnEditor', 'transx', 'ExtMFD', 'Multistage2015_MFD'])
    enable_modules(['D3D9Client', 'GenericCamera', 'transx', 'OrbiterSound', 'XRSound', 'DX9ExtMFD', 'ScnEditor', 'Multistage2015_MFD'], enable_for_orbiter_ng=True)

def requires_fresh_install():
    return False

if __name__ == '__main__':
    main()