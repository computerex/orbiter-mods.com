import _ from 'underscore';
import $ from 'jquery';
import Orb from './Orb';
export class ModViewer {
    constructor() {
        console.log('mod viewer');
        // get mod id from url of format /view/:mod_id/:slug
        const mod_id = window.location.pathname.split('/')[2];
        this.mod_id = mod_id;
        console.log(mod_id);
    };

    render(mod_info) {
        const picture_html = 
            `<div>
            <div class="mod-picture">
                <img src="${mod_info.picture_link}" />
            </div>
        `;
        $('.root').append(`
            <div>
                <h1 style="margin-bottom:-5px;">${mod_info.name}</h1>
                <small>${mod_info.owner}</small>
            </div>`);

        $('.root').append(mod_info.description);
        $('.root').append(picture_html);
    };

    run() {
        Orb.checkSession((loggedIn) => {
            let url = `/mod/${this.mod_id}/info`;
            if (loggedIn) {
                url += `?api_key=${Orb.api_key}`;
            }
            $.get(url, (data) => {
                this.render(data);
                console.log(data);
            }).fail((data)=> {
                console.log(data.responseJSON.error);
            });
        });
    };
};