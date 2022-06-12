def main(download_from_ohm, download_zip, install_zip, enable_modules):
    download_zip('https://www.alteaaerospace.com/ccount/click.php?id=3', 'XR2 Ravenstar.zip')
    install_zip('XR2 Ravenstar.zip')
    download_zip('https://www.alteaaerospace.com/ccount/click.php?id=55', 'XRSound.zip')
    install_zip('XRSound.zip')
    download_zip('https://www.orbiter-forum.com/resources/soundbridge.204/download', 'Soundbridge.zip')
    install_zip('Soundbridge.zip')

    enable_modules(['OrbiterSound', 'XRSound'])

def requires_fresh_install():
    return True

if __name__ == '__main__':
    main()