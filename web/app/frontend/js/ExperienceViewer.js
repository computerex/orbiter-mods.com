import _ from 'underscore';
import $ from 'jquery';

import { ExperienceUploader } from './ExperienceUploader';

export class ExperienceViewer {
    constructor() {
        this.experiences = [];
        this.uploader = new ExperienceUploader(this);
    };

    addExperience(experience) {
        this.experiences.push(experience);
    };

    selectExperience(experienceId) {
        $('.right-pane').empty();
        
        if (experienceId == 0) {
            console.log('selecting upload page');
            this.uploader.run();
            return;
        }
        else {
            const experience = this.experiences.filter(experience => experience.id == experienceId)[0];
            const html =
`
<pre>
${experience.experience_script}
</pre>`;
            $('.right-pane').append(html);
            $('.experience').removeClass('selected');
            $(`.experience[data-experience-id=${experienceId}]`).addClass('selected');
        }
    };

    render() {
        $('.experiences').empty();

        const html = `
                <div data-experience-id="0" class="experience" style="text-align:center;">
                    <i class="fa fa-plus" style="font-size:2em;color:#14854f;"></i>
                </div>
            `;

        $('.experiences').append(html);
        
        // iterate over this.experiences
        for (let key in this.experiences) {
            const experience = this.experiences[key];
            const featured_html = experience.featured ? '<i class="fa fa-star" style="color:#14854f;"></i>' : '';
            const html = `
                <div data-experience-id="${experience.id}" class="experience">
                    <div class="experience-title">${experience.name}</div>
                    ${featured_html}
                    <div class="experience-description">${experience.description}</div>
                    <div class="experience-links">${experience.links}</div>
                </div>
            `;
            $('.experiences').append(html);
        }
        // click handler on experience
        $('.experience').off('click');
        $('.experience').on('click', (event) => {
            const experienceId = $(event.currentTarget).data('experience-id');
            this.selectExperience(experienceId);
        });
        $(`.experience[data-experience-id=0]`).trigger('click');
    };

    run() {
        // check if this.experiences is empty
        if (this.experiences.length === 0) {
            $.get('/fetch_experiences', (data) => {
                this.experiences = data;
                this.render();
            });
        }
    };
};