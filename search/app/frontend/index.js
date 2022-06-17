
import { AddonSearch } from './AddonSearch';
const location = window.location.pathname;

switch(location) {
    case '/':
        const addonSearch = new AddonSearch();
        addonSearch.run();
        break;
};