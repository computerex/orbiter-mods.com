import _ from 'underscore';
import $ from 'jquery';

export class ExperienceViewer {
    constructor() {
        this.experiences = {};
        $.get('/fetch_experiences', (data) => {
            this.experiences = data;
            console.log(data);
            this.render();
          });
    };

    selectExperience(experienceId) {
        $('.experience-script').empty();
        const experience = this.experiences.filter(experience => experience.id == experienceId)[0];
        console.log(experienceId);
        console.log(experience);
        const html = `
            <pre>
                ${experience.experience_script}
            </pre>`;
        $('.experience-script').append(html);
        $('.experience').removeClass('selected');
        $(`.experience[data-experience-id=${experienceId}]`).addClass('selected');
    };

    render() {
        $('.experiences').empty();
        
        // iterate over this.experiences
        for (let key in this.experiences) {
            const experience = this.experiences[key];

            const html = `
                <div data-experience-id=${experience.id} class="experience">
                    <div class="experience-title">${experience.name}</div>
                    <div class="experience-description">${experience.description}</div>
                    <div class="experience-links">${experience.links}</div>
                </div>
            `;
            $('.experiences').append(html);
        }
        // click handler on experience
        $('.experience').on('click', (event) => {
            console.log(event);
            const experienceId = $(event.currentTarget).data('experience-id');
            this.selectExperience(experienceId);
        });
    };

    run() {

    };
};