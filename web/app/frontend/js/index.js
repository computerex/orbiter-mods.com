
import { AddonSearch } from './AddonSearch';
import { UploadAddon } from './UploadAddon';
import { ExperienceManager } from './ExperienceManager';
import { LoginRegister } from './LoginRegister';
import { ModViewer } from './ModViewer';
import Orb from './Orb';
const location = window.location.pathname;

switch(true) {
    case location == '/':
        const addonSearch = new AddonSearch();
        addonSearch.run();
        break;
    case /^\/orb$/i.test(location) || location == '/orb.html':
        const experienceManager = new ExperienceManager();
        experienceManager.run();
        break;
    case /^\/upload$/i.test(location) ||  location == '/upload.html':
        const uploadAddon = new UploadAddon();
        uploadAddon.run();
        break;
    case /^\/login_register$/i.test(location) || location == '/login_register.html':
        Orb.checkSession((loggedIn) => {
            if (!loggedIn) {
                const loginRegister = new LoginRegister(null, '.login-form', (data) => {
                    if (data.success) {
                        window.location.href = '/';
                    }
                });
                loginRegister.run();
            } else {
                window.location.href = '/';
            }
        });
        break;
    case /^\/view\/\d/i.test(location) || location == '/view.html':
        const modViewer = new ModViewer();
        modViewer.run();
        break;
};