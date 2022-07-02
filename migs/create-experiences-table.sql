-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: mysqldb
-- Generation Time: Jul 02, 2022 at 07:21 PM
-- Server version: 8.0.21
-- PHP Version: 8.0.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `app`
--

-- --------------------------------------------------------

--
-- Table structure for table `experiences`
--

CREATE TABLE `experiences` (
  `id` int NOT NULL,
  `name` varchar(64) NOT NULL,
  `links` text NOT NULL,
  `experience_script` text NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `experiences`
--

INSERT INTO `experiences` (`id`, `name`, `links`, `experience_script`, `description`) VALUES
(1, 'Orbiter Essentials', 'XR2 Ravenstar and XRSound by dbeachy, Soundbridge by Face, D3D9 client + Microtextures by Jarmonik, Multistage by fred18', 'def main(orb):\r\n    orb.download_zip(\'https://www.alteaaerospace.com/ccount/click.php?id=3\', \'XR2 Ravenstar.zip\')\r\n    orb.install_zip(\'XR2 Ravenstar.zip\')\r\n    orb.download_zip(\'https://www.alteaaerospace.com/ccount/click.php?id=55\', \'XRSound.zip\')\r\n    orb.install_zip(\'XRSound.zip\')\r\n    print(\'downloading from OF\')\r\n    orb.download_from_of(\'https://www.orbiter-forum.com/resources/soundbridge.204/download\', \'SoundBridge1.1.zip\')\r\n    orb.install_zip(\'SoundBridge1.1.zip\')\r\n    orb.download_zip(\'http://users.kymp.net/~p501474a/D3D9Client/D3D9ClientR4.25-forOrbiter2016(r1446).zip\', \'D3D9ClientR4.25-forOrbiter2016(r1446).zip\')\r\n    orb.install_zip(\'D3D9ClientR4.25-forOrbiter2016(r1446).zip\')\r\n    orb.download_zip(\'http://users.kymp.net/~p501474a/D3D9Client/MicroTextures.zip\', \'MicroTextures.zip\')\r\n    orb.install_zip(\'MicroTextures.zip\')\r\n\r\n    orb.download_from_of(\'https://www.orbiter-forum.com/resources/multistage2015-for-orbiter-2016.398/download\', \'Multistage2015_forOrbiter2016.zip\')\r\n    orb.install_zip(\'Multistage2015_forOrbiter2016.zip\')\r\n\r\n    orb.edit_cfg_file_add_line(\'Orbiter.cfg\', \'DisableFontSmoothing = FALSE\')\r\n    orb.edit_cfg_file_add_line(\'Orbiter_NG.cfg\', \'DisableFontSmoothing = FALSE\')\r\n\r\n    orb.enable_modules([\'OrbiterSound\', \'XRSound\', \'ScnEditor\', \'transx\', \'ExtMFD\', \'Multistage2015_MFD\'])\r\n    orb.enable_modules([\'D3D9Client\', \'GenericCamera\', \'transx\', \'OrbiterSound\', \'XRSound\', \'DX9ExtMFD\', \'ScnEditor\', \'Multistage2015_MFD\'], True)\r\n\r\ndef requires_fresh_install():\r\n    return False\r\n\r\nif __name__ == \'__main__\':\r\n    main()', 'XR2 Ravenstar and XRSound by Doug Beachy (dbeachy):\r\nhttps://www.alteaaerospace.com/ccount/click.php?id=3\r\nhttps://www.alteaaerospace.com/ccount/click.php?id=55\r\n\r\nSoundbridge by Face:\r\nhttps://www.orbiter-forum.com/resources/soundbridge.204/download\r\n\r\nD3D9 client + Microtextures by Jarmonik:\r\nhttp://users.kymp.net/~p501474a/D3D9Client/D3D9ClientR4.25-forOrbiter2016(r1446).zip\r\nhttp://users.kymp.net/~p501474a/D3D9Client/MicroTextures.zip\r\n\r\nMultistage by fred18:\r\nhttps://www.orbiter-forum.com/resources/multistage2015-for-orbiter-2016.398/download'),
(2, 'SpaceX Missions', 'All thanks to BrianJ,  Dr.S, Donamy, Marg, francisdrake, Barry, Fred18, IronRain, Kyle, DaveS, SiameseCat, David413, GLS, Felix24, Don', '# by lunar/lunar industries\r\ndef main(orb):\r\n    # All thanks to BrianJ,  Dr.S, Donamy, Marg, francisdrake, Barry, Fred18, IronRain, Kyle, DaveS, SiameseCat, David413, \r\n    # GLS, Felix24, Don\r\n    #TODO : add credits for each url separately too\r\n\r\n    orb.download_from_of(\'https://www.orbiter-forum.com/resources/falcon9-for-orbiter2016.291/download\' , \'falcon9_o2016_210425.zip\')\r\n    orb.install_zip(\'falcon9_o2016_210425.zip\')\r\n\r\n    \r\n    orb.download_from_of(\'https://www.orbiter-forum.com/resources/lc39a-spacex.3092/download\' , \'lc39a_spacex_190407.zip\')\r\n    orb.install_zip(\'lc39a_spacex_190407.zip\')\r\n\r\n    orb.download_from_of(\'https://www.orbiter-forum.com/resources/falcon9-block4.403/download\', \'falcon9_block4_180714.zip\')\r\n    orb.install_zip(\'falcon9_block4_180714.zip\')\r\n\r\n    orb.download_from_of(\'https://www.orbiter-forum.com/resources/starlink.339/download\' , \'starlink_190526.zip\')\r\n    orb.install_zip(\'starlink_190526.zip\')\r\n\r\n    orb.download_from_of(\'https://www.orbiter-forum.com/resources/cargo-dragon-for-orbiter2016.805/download\', \'cargo_dragon_o2016_170811.zip\')\r\n    orb.install_zip(\'cargo_dragon_o2016_170811.zip\')\r\n\r\n    orb.download_from_of(\'https://www.orbiter-forum.com/resources/canadarm2-v-4-0.1867/download\' , \'Canadarm2v4.zip\')\r\n    orb.install_zip(\'Canadarm2v4.zip\')\r\n\r\n    #subdir\r\n    orb.download_from_of(\'https://www.orbiter-forum.com/resources/ssrmsd-dll-update.1221/download\' , \'SSRMSD2016update.zip\')\r\n    orb.install_zip(\'SSRMSD2016update.zip\', \'SSRMSD2016update\')\r\n\r\n    orb.download_from_of(\'https://www.orbiter-forum.com/resources/crew-dragon-dm2.312/download\', \'dm2_220611.zip\')\r\n    orb.install_zip(\'dm2_220611.zip\')\r\n\r\n    orb.download_from_of(\'https://www.orbiter-forum.com/resources/crew-dragon-inspiration4.876/download\' , \'inspiration4_220611.zip\')\r\n    orb.install_zip(\'inspiration4_220611.zip\')\r\n\r\n    orb.download_from_of(\'https://www.orbiter-forum.com/resources/falconheavy-for-orbiter2016.3287/download\' , \'falconheavy_o2016_211123.zip\')\r\n    orb.install_zip(\'falconheavy_o2016_211123.zip\')\r\n\r\n    orb.download_from_of(\'https://www.orbiter-forum.com/resources/moonship.833/download\' , \'Moonship-05.zip\')\r\n    orb.install_zip(\'Moonship-05.zip\')\r\n\r\n    orb.download_from_of(\'https://www.orbiter-forum.com/resources/starship-sn15.2233/download\' , \'starship_sn15_210429.zip\')\r\n    orb.install_zip(\'starship_sn15_210429.zip\')\r\n\r\n    orb.download_from_of(\'https://www.orbiter-forum.com/resources/spacex-starship-wip.126/download\' , \'spacex_starship_220328.zip\') \r\n    orb.install_zip(\'spacex_starship_220328.zip\')\r\n\r\n    orb.download_from_of(\'https://www.orbiter-forum.com/resources/boca-chica-base.778/download\' , \'boca_chica_base_220304.zip\')  \r\n    orb.install_zip(\'boca_chica_base_220304.zip\')\r\n\r\n    orb.enable_modules([\'CrewDragonMFD\'])\r\n    orb.enable_modules([\'CrewDragonMFD\'], True)\r\n\r\n    orb.install_orbiter_mods_experience(1)\r\n\r\n\r\n\r\ndef requires_fresh_install():\r\n    return False\r\n\r\nif __name__ == \'__main__\':\r\n    pass', 'Experience prepared by lunar industries');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `experiences`
--
ALTER TABLE `experiences`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `experiences`
--
ALTER TABLE `experiences`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
