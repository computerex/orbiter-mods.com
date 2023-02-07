import _ from 'underscore';
import $ from 'jquery';
import Orb from './Orb';
export class ModViewer {
    constructor() {
        // get mod id from url of format /view/:mod_id/:slug
        const mod_id = window.location.pathname.split('/')[2];
        this.mod_id = mod_id;
    };

    render(mod_info) {
        // set page title to mod_info.name
        document.title = mod_info.name;
        document.querySelector('meta[name="description"]').setAttribute("content", mod_info.description);
        // set og:title
        document.querySelector('meta[property="og:title"]').setAttribute("content", mod_info.name);
        if (mod_info.restricted) {
            $('.root').append(`
                <h1 style="display:inline-block;">${mod_info.name}</h1>
                <small>added by ${mod_info.owner}</small>`);
        } else {
            $('.root').append(`
                <a href="/mod/${this.mod_id}"><h1 style="display:inline-block;">Download ${mod_info.name}</h1></a>
                <small>added by ${mod_info.owner}</small>`);
        }

        if (mod_info.is_owner) {
            $('.root').append(` <a href="/upload?mod_id=${this.mod_id}">(edit mod)</a>`);
        }
        
        $('.root').append(mod_info.description);

        if (mod_info.picture_link) {
            $('.root').append( `
                <div class="mod-picture">
                    <img src="${mod_info.picture_link}" />
                </div>`);
        }
    };

    run() {
        Orb.checkSession((loggedIn) => {
            let url = `/mod/${this.mod_id}/info`;
            if (loggedIn) {
                url += `?api_key=${Orb.api_key}`;
            }
            $.get(url, (data) => {
                this.render(data);
            }).fail((data)=> {
                alert(data.responseJSON.error);
            });
        });
    };
};