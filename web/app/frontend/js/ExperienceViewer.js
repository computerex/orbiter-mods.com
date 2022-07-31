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

    updateExperience(experience) {
        const index = _.findIndex(this.experiences, {id: experience.id});
        this.experiences[index] = experience;
    };

    selectExperience(experienceId) {
        $('.right-pane').empty();
        
        window.history.replaceState(null, null, `?id=${experienceId}`);
        if (experienceId == 0) {
        }
        $('.experience').removeClass('selected');
        $(`.experience[data-experience-id=${experienceId}]`).addClass('selected');
        this.uploader.run(experienceId, this.experiences);
    };

    render(experienceId) {
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

        if (experienceId && experienceId != 0) {
            this.click_experience(experienceId);   
        } else {
            // get experience id through from 'id' url query string
            let experienceId = parseInt(window.location.search.split('=')[1] || 0);
            // get all the ids from this.experiences
            const ids = this.experiences.map(experience => parseInt(experience.id));
            if (ids.includes(experienceId)) {
                this.click_experience(experienceId);
            } else {
                this.click_experience(0);
            }
        }
    };

    click_experience(experienceId) {
        $(`.experience[data-experience-id=${experienceId}]`).trigger('click');
        $(`.experience[data-experience-id=${experienceId}]`).addClass('selected');
    };

    run(experienceId) {
        // check if this.experiences is empty
        if (this.experiences.length === 0) {
            $.get('/fetch_experiences', (data) => {
                this.experiences = data;
                this.render(experienceId);
            });
        }
    };
};