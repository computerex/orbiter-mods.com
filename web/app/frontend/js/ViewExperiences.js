import _ from 'underscore';
import $ from 'jquery';

export class ViewExperiences {
    constructor() {
        this.experiences = {};
        $.get('/fetch_experiences', (data) => {
            console.log(data);
          });
    };

    render(results) {
    };

    run() {

    };
};