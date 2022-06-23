# by lunar/lunar industries
def main(orb):
    # All thanks to BrianJ,  Dr.S, Donamy, Marg, francisdrake, Barry, Fred18, IronRain, Kyle, DaveS, SiameseCat, David413, 
    # GLS, Felix24, Don
    #TODO : add credits for each url separately too

    orb.download_from_of('https://www.orbiter-forum.com/resources/falcon9-for-orbiter2016.291/download' , 'falcon9_o2016_210425.zip')
    orb.install_zip('falcon9_o2016_210425.zip')

    
    orb.download_from_of('https://www.orbiter-forum.com/resources/lc39a-spacex.3092/download' , 'lc39a_spacex_190407.zip')
    orb.install_zip('lc39a_spacex_190407.zip')

    orb.download_from_of('https://www.orbiter-forum.com/resources/falcon9-block4.403/download', 'falcon9_block4_180714.zip')
    orb.install_zip('falcon9_block4_180714.zip')

    orb.download_from_of('https://www.orbiter-forum.com/resources/starlink.339/download' , 'starlink_190526.zip')
    orb.install_zip('starlink_190526.zip')

    orb.download_from_of('https://www.orbiter-forum.com/resources/cargo-dragon-for-orbiter2016.805/download', 'cargo_dragon_o2016_170811.zip')
    orb.install_zip('cargo_dragon_o2016_170811.zip')

    orb.download_from_of('https://www.orbiter-forum.com/resources/canadarm2-v-4-0.1867/download' , 'Canadarm2v4.zip')
    orb.install_zip('Canadarm2v4.zip')

    #subdir
    orb.download_from_of('https://www.orbiter-forum.com/resources/ssrmsd-dll-update.1221/download' , 'SSRMSD2016update.zip')
    orb.install_zip('SSRMSD2016update.zip', 'SSRMSD2016update')

    orb.download_from_of('https://www.orbiter-forum.com/resources/crew-dragon-dm2.312/download', 'dm2_220611.zip')
    orb.install_zip('dm2_220611.zip')

    orb.download_from_of('https://www.orbiter-forum.com/resources/crew-dragon-inspiration4.876/download' , 'inspiration4_220611.zip')
    orb.install_zip('inspiration4_220611.zip')

    orb.download_from_of('https://www.orbiter-forum.com/resources/falconheavy-for-orbiter2016.3287/download' , 'falconheavy_o2016_211123.zip')
    orb.install_zip('falconheavy_o2016_211123.zip')

    orb.download_from_of('https://www.orbiter-forum.com/resources/moonship.833/download' , 'Moonship-05.zip')
    orb.install_zip('Moonship-05.zip')

    orb.download_from_of('https://www.orbiter-forum.com/resources/starship-sn15.2233/download' , 'starship_sn15_210429.zip')
    orb.install_zip('starship_sn15_210429.zip')

    orb.download_from_of('https://www.orbiter-forum.com/resources/spacex-starship-wip.126/download' , 'spacex_starship_220328.zip') 
    orb.install_zip('spacex_starship_220328.zip')

    orb.download_from_of('https://www.orbiter-forum.com/resources/boca-chica-base.778/download' , 'boca_chica_base_220304.zip')  
    orb.install_zip('boca_chica_base_220304.zip')

    orb.enable_modules(['CrewDragonMFD'])
    orb.enable_modules(['CrewDragonMFD'], True)



def requires_fresh_install():
    return False

if __name__ == '__main__':
    pass