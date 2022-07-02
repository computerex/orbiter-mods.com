
import { AddonSearch } from './AddonSearch';
import { UploadAddon } from './UploadAddon';
import { ViewExperiences } from './ViewExperiences';
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
    case '/experiences.html':
        const viewExperiences = new ViewExperiences();
        viewExperiences.run();
        break;
};