import _ from 'underscore';
import $ from 'jquery';

import { ExperienceViewer } from './ExperienceViewer';
import { Experienceuploader } from './ExperienceUploader';

export class ExperienceManager {
    constructor() {
        this.experienceViewer = new ExperienceViewer();
    };

    render(results) {
    };

    run() {
        this.experienceViewer.run();
    };
};