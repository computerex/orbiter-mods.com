import _ from 'underscore';
import $ from 'jquery';
import Orb from './Orb';
export class ModViewer {
    constructor() {
        console.log('mod viewer');
        // get mod id from url of format /view/:mod_id/:slug
        const mod_id = window.location.pathname.split('/')[2];
        console.log(mod_id);
    };

    render(results) {
        
    };

    run() {
        $(() => {
            Orb.checkSession((loggedIn) => {
                if (loggedIn) {
                    $('#upload-addon').show();
                    $('.login-register-link').hide();
                    $('form').attr('action', `/upload_mod?api_key=${Orb.api_key}`);
                } else {
                    $('#upload-addon').hide();
                    $('.login-register-link').show();
                }
            });
        });
    };
};