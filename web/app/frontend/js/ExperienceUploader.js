import _ from 'underscore';
import $ from 'jquery';

import { LoginRegister } from './LoginRegister';
import { ExperienceViewer } from './ExperienceViewer';
import Orb from './Orb';



export class ExperienceUploader {
    constructor(viewer) {
        this.viewer = viewer;
        this.script_default = 
`# This is a comment! It is ignored by orb
# You can use comments to share links and explain stuff

def main(orb):
    # This is a recipe for orb installer
    # It downloads and installs the XR2 Ravenstar
    # By Doug Beachy and Coolhand

    orb.download_zip('https://www.alteaaerospace.com/ccount/click.php?id=3', 'XR2 Ravenstar.zip')
    orb.install_zip('XR2 Ravenstar.zip')

def requires_fresh_install():
    return False`;
    };

    render(loggedin) {
        $('.right-pane').empty();
        $('.right-pane').append(`<h1>Add orb mod bundle</h1>`);
        if (!loggedin) {
            $('.right-pane').append(`<small class="login-link">login or register</small>`);
        } else {
            $('.right-pane').append(`<small class="user-info">logged in as ${Orb.email}</small>`);
        }

        const html = `
            <label for="name">mod bundle name:</label>
            <input id="name" type="text" placeholder="eg. Orbiter Essentials" />

            <label for="description">bundle description:</label>
            <textarea id="description" style="width:100%;height:100px;"></textarea>

            <label for="links">links and credits:</label>
            <textarea id="links" style="width:100%;height:100px;"></textarea>

            <label for="script">bundle recipe:</label>
            <textarea id="script" style="width:100%;height:200px;"></textarea>
            <br />
        `;

        $('.right-pane').append(html);
        if (loggedin) {
            $('.right-pane').append(`<button id="add" id="experience-upload">Add</button>`);
        }

        $('#script').val(this.script_default);

        $('.login-link').on('click', () => {
            const login_register = new LoginRegister(this.viewer);
            login_register.run();
        });

        $('#add').on('click', () => {
            Orb.checkSession((session) => {
                if (session) {
                    // extract name, description, links, script
                    const name = $('#name').val();
                    const description = $('#description').val();
                    const links = $('#links').val();
                    const script = $('#script').val();
                    // add experience by posting to /experience endpoint
                    $.post(`/experience?api_key=${Orb.api_key}`, {
                        name: name,
                        description: description,
                        links: links,
                        experience_script: script
                    }, (data) => {
                        if (data.success) {
                            // add experience to viewer
                            this.viewer.addExperience({
                                name,
                                description,
                                links,
                                experience_script: script,
                                id: data.id
                            });
                            this.viewer.render();
                        } else {
                            alert(data.error);
                        }
                    });
                } else {
                    alert('You must be logged in to add an orb mod bundle');
                }
            });
        });
    };

    run() {
        Orb.checkSession((session) => {
            if (session) {
                Orb.get_user_experiences(() => {
                    this.render(true);
                });
            } else {
                this.render(session);
            }
        });
    };
};