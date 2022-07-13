import _ from 'underscore';
import $ from 'jquery';
import Orb from './Orb';
export class UploadAddon {
    constructor() {
        $(document).ready(function(){
            $('form input[type=file]').change(function () {
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

            $("form").submit(function(e) {
                e.preventDefault();    
                var formData = new FormData(this);

                $.ajax({
                    url: `/upload_mod?api_key=${Orb.api_key}`,
                    type: 'POST',
                    data: formData,
                    success: function (data) {
                        if (data.error) {
                            $('.errors').text(data.error);
                        } else if (data.success) {
                            alert('mod uploaded');
                        }
                    },
                    error: function(data) {
                        console.log(data);
                        console.log('foobar');
                        if (data.responseJSON) {
                            console.log(data.responseJSON);
                            console.log(data.responseJSON.error);
                            $('.errors').text(data.responseJSON.error);
                        } else {
                            console.log(JSON.parse(JSON.stringify(data)));
                        }
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });
        });
    };

    render(results) {
        
    };

    run() {
    };
};