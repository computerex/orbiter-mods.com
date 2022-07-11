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

    get_experience_data_from_ui() {
        const name = $('#name').val();
        const description = $('#description').val();
        const links = $('#links').val();
        const script = $('#script').val();

        return {
            name,
            description,
            links,
            script
        };
    };

    update_experience(experienceId, experience) {
        Orb.checkSession((session) => {
            if (session) {
                // extract name, description, links, script
                const experience_data = this.get_experience_data_from_ui();
                // add experience by posting to /experience endpoint
                $.post(`/update_experience?api_key=${Orb.api_key}&id=${experienceId}`, {
                    name: experience_data['name'],
                    description: experience_data['description'],
                    links: experience_data['links'],
                    experience_script: experience_data['script']
                }, (data) => {
                    if (data.success) {
                        // add experience to viewer
                        this.viewer.updateExperience({
                            name: experience_data['name'],
                            description: experience_data['description'],
                            links: experience_data['links'],
                            experience_script: experience_data['script'],
                            id: experienceId,
                            featured:experience['featured']
                        });
                        this.viewer.render(experienceId);
                    } else {
                        alert(data.error);
                    }
                });
            } else {
                alert('You must be logged in to update an orb mod bundle');
            }
        });
    };

    add_experience() {
        Orb.checkSession((session) => {
            if (session) {
                // extract name, description, links, script
                const experience_data = this.get_experience_data_from_ui();
                // add experience by posting to /experience endpoint
                $.post(`/experience?api_key=${Orb.api_key}`, {
                    name: experience_data['name'],
                    description: experience_data['description'],
                    links: experience_data['links'],
                    experience_script: experience_data['script']
                }, (data) => {
                    if (data.success) {
                        // add experience to viewer
                        this.viewer.addExperience({
                            name: experience_data['name'],
                            description: experience_data['description'],
                            links: experience_data['links'],
                            experience_script: experience_data['script'],
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
    };
    
    render_experience_form(loggedin, experienceId, experience) {
        let name = "";
        let description = "";
        let links = "";
        let script = this.script_default;

        if (experienceId != 0) {
            $('.right-pane').append(`<h1>Update ${experience.name}</h1>`);
            name = experience.name;
            description = experience.description;
            links = experience.links;
            script = experience.experience_script;
        } else {
            $('.right-pane').append(`<h1>Add orb mod bundle</h1>`);
        }
        
        if (!loggedin) {
            $('.right-pane').append(`<small class="login-link">login or register</small>`);
        } else {
            $('.right-pane').append(`<small class="user-info">logged in as ${Orb.email}</small>`);
        }

        const html = `
            <label for="name">mod bundle name:</label>
            <input id="name" type="text" placeholder="eg. Orbiter Essentials" value="${name}" />

            <label for="description">bundle description:</label>
            <textarea id="description" style="width:100%;height:100px;">${description}</textarea>

            <label for="links">links and credits:</label>
            <textarea id="links" style="width:100%;height:100px;">${links}</textarea>

            <label for="script">bundle recipe:</label>
            <textarea id="script" style="width:100%;height:200px;">${script}</textarea>
            <br />
        `;

        $('.right-pane').append(html);

        if (loggedin) {
            if (experienceId != 0) {
                $('.right-pane').append(`<button id="update">Update</button>`);
            } else {
                $('.right-pane').append(`<button id="add">Add</button>`);
            }
        }

        $('.login-link').on('click', () => {
            const login_register = new LoginRegister(this.viewer);
            login_register.run();
        });

        $('#add').on('click', () => {
            this.add_experience();
        });

        $('#update').on('click', () => {
            this.update_experience(experienceId, experience);
        });
    };

    render(loggedin, experienceId, experiences) {
        $('.right-pane').empty();
        if (experienceId == 0) {
            this.render_experience_form(loggedin, experienceId);
        } else {
            const experience = experiences.filter(experience => experience.id == experienceId)[0];
            if (loggedin && Orb.user_experiences.indexOf(experienceId) !== -1) {
                this.render_experience_form(loggedin, experienceId, experience);
            } else {
                const html =
`
<pre>
${experience.experience_script}
</pre>`;
                $('.right-pane').append(html);        
           }
        }
    };

    run(experienceId, experiences) {
        Orb.checkSession((session) => {
            if (session) {
                Orb.get_user_experiences((user_experiences) => {
                    this.render(true, experienceId, experiences);
                });
            } else {
                this.render(false, experienceId, experiences);
            }
        });
    };
};