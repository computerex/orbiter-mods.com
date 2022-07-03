def main(orb):
    orb.set_scn_blacklist([
        'IMFD51/AMSO/AMSO-Apollo11-MCC.scn',
        'IMFD51/AMSO/AMSO-Apollo11-TLI.scn',
        'IMFD51/NASSP/A11TLI.scn'
    ])

    #XR2 Ravenstar
    #By Doug Beachy and Coolhand
    orb.download_zip('https://www.alteaaerospace.com/ccount/click.php?id=3', 'XR2 Ravenstar.zip')
    orb.install_zip('XR2 Ravenstar.zip')

    #XRSound
    #By Doug Beachy
    orb.download_zip('https://www.alteaaerospace.com/ccount/click.php?id=55', 'XRSound.zip')
    orb.install_zip('XRSound.zip')

    #SoundBridge
    #By face
    orb.download_from_of('https://www.orbiter-forum.com/resources/soundbridge.204/download', 'SoundBridge1.1.zip')
    orb.install_zip('SoundBridge1.1.zip')
    
    #D3D9Client R4.25 for Orbiter 2016 (r1446)
    #By Jarmonik
    orb.download_zip('http://users.kymp.net/~p501474a/D3D9Client/D3D9ClientR4.25-forOrbiter2016(r1446).zip', 'D3D9ClientR4.25-forOrbiter2016(r1446).zip')
    orb.install_zip('D3D9ClientR4.25-forOrbiter2016(r1446).zip')
    
    #MicroTextures
    #By Jarmonik
    orb.download_zip('http://users.kymp.net/~p501474a/D3D9Client/MicroTextures.zip', 'MicroTextures.zip')
    orb.install_zip('MicroTextures.zip')

    #Multistage2015 for Orbiter 2016
    #By fred18
    orb.download_from_of('https://www.orbiter-forum.com/resources/multistage2015-for-orbiter-2016.398/download', 'Multistage2015_forOrbiter2016.zip')
    orb.install_zip('Multistage2015_forOrbiter2016.zip')

    #ModuleMessagingExtMFD 
    #By Enjo and ADSWNJ
    orb.download_from_of('https://www.orbiter-forum.com/resources/modulemessagingext-v2-1d-for-orbiter-2016.121/download' , 'ModuleMessagingExt v2.1d.zip')
    orb.install_zip('ModuleMessagingExt v2.1d.zip')


    #TransX2 
    #By Enjo, Duncan Sharpe, Steve Arch, dgatsoulis, atomicdryad 
    orb.download_from_of('https://www.orbiter-forum.com/resources/transx-2018-05-06-mmext2-for-orbiter-2016.1918/download' , 'TransX-2018.05.06-VCpp-2016.zip')
    orb.install_zip('TransX-2018.05.06-VCpp-2016.zip')


    #Glideslope 
    #By ADSWNJ, based on the original Glideslope by Chris Jeppesen
    orb.download_from_of('https://www.orbiter-forum.com/resources/glideslope-2-7-for-orbiter-2016.1093/download' , 'Glideslope 2.7 for Orbiter 2016.zip')
    orb.install_zip('Glideslope 2.7 for Orbiter 2016.zip')


    #BaseSyncMFD 
    #By ADSWNJ
    orb.download_from_of('https://www.orbiter-forum.com/resources/basesyncmfd-3-3-for-orbiter-2016.2705/download' , 'BaseSync 3.3 for Orbiter 2016.zip')
    orb.install_zip('BaseSync 3.3 for Orbiter 2016.zip')


    #AeroBrakeMFD 
    #By gp
    orb.download_from_of('https://www.orbiter-forum.com/resources/aerobrakemfd.1171/download' , 'AeroBrake0.96.2.zip')
    orb.install_zip('AeroBrake0.96.2.zip')


    #LaunchMFD 
    #By Vanguard, Enjo, Pawel Stiasny, Mohd Ali, Chris Jeppesen, Steve Arch, CJ Plooy, Tim Blaxland
    orb.download_from_of('https://www.orbiter-forum.com/resources/launch-mfd-v-1-6-6-for-orbiter-2016.2192/download' , 'LaunchMFD-v.1.6.6-2016.zip')
    orb.install_zip('LaunchMFD-v.1.6.6-2016.zip')


    #PursuitMFD_2016 
    #By Rawash 
    orb.download_from_of('https://www.orbiter-forum.com/resources/pursuitmfd-2016.3096/download' , 'PursuitMFD_171119.zip')
    orb.install_zip('PursuitMFD_171119.zip')


    #LunarTransferMFD 
    #By jarmonik
    orb.download_zip('http://users.kymp.net/p501474a/Orbiter/LTMFD16.zip', 'LTMFD16.zip')
    orb.install_zip('LTMFD16.zip')


    #InterMFD57 
    #By jarmonik
    orb.download_zip('http://users.kymp.net/p501474a/Orbiter/IMFD57.zip', 'IMFD57.zip')
    orb.install_zip('IMFD57.zip')


    #HUDDrawer 
    #By enjo, Steve Arch, 
    orb.download_from_of('https://www.orbiter-forum.com/resources/huddrawer-sdk-v-0-4-for-orbiter-2016.2873/download' , 'HUDDrawerSDK-v.0.4-2016.zip')
    orb.install_zip('HUDDrawerSDK-v.0.4-2016.zip')


    #BurnTimeMFD 
    #By enjo
    orb.download_from_of('https://www.orbiter-forum.com/resources/burntimecalcmfd-btc-3-1-for-orbiter-2016.736/download' , 'BurnTimeCalcMFD-v.3.1.0-2016.zip')
    orb.install_zip('BurnTimeCalcMFD-v.3.1.0-2016.zip')

    orb.edit_cfg_file_add_line('Orbiter.cfg', 'DisableFontSmoothing = FALSE')
    orb.edit_cfg_file_add_line('Orbiter_NG.cfg', 'DisableFontSmoothing = FALSE')

    orb.enable_modules(['OrbiterSound', 'XRSound', 'ScnEditor', 'transx', 'ExtMFD', 'Multistage2015_MFD'])
    orb.enable_modules(['D3D9Client', 'GenericCamera', 'transx', 'OrbiterSound', 'XRSound', 'DX9ExtMFD', 'ScnEditor', 'Multistage2015_MFD'], True)

    orb.enable_modules(['ModuleMessagingExtMFD','TransX2','Glideslope','BaseSyncMFD','AeroBrakeMFD','LaunchMFD','PursuitMFD_2016','LunarTransferMFD','InterMFD57','HUDDrawer','BurnTimeMFD'], True)
    orb.enable_modules(['ModuleMessagingExtMFD','TransX2','Glideslope','BaseSyncMFD','AeroBrakeMFD','LaunchMFD','PursuitMFD_2016','LunarTransferMFD','InterMFD57','HUDDrawer','BurnTimeMFD'])
    

def requires_fresh_install():
    return False

if __name__ == '__main__':
    main()