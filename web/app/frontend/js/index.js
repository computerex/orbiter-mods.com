
import { AddonSearch } from './AddonSearch';
import { UploadAddon } from './UploadAddon';
import { ExperienceManager } from './ExperienceManager';
const location = window.location.pathname;

switch(location) {
    case '/':
        const addonSearch = new AddonSearch();
        addonSearch.run();
        break;
    case '/upload-addon.html':
        const uploadAddon = new UploadAddon();
        uploadAddon.run();
        break;
    case '/orb.html':
        const experienceManager = new ExperienceManager();
        experienceManager.run();
        break;
};