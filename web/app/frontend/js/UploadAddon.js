import _ from 'underscore';
import $ from 'jquery';
import Orb from './Orb';
export class UploadAddon {
    constructor() {
        $(document).ready(function(){
            $('form input').change(function () {
              $('form p').text(this.files.length + " file(s) selected");
            });

            console.log('checking session');
            Orb.checkSession((loggedIn) => {
                console.log('session', loggedIn);
                if (loggedIn) {
                    $('#upload-addon').show();
                    $('form').attr('action', `/upload_mod?api_key=${Orb.api_key}`);
                } else {
                    $('#upload-addon').hide();
                }
            });
        });
    };

    render(results) {
        
    };

    run() {
    };
};