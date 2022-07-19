import _ from 'underscore';
import $ from 'jquery';
import Orb from './Orb';
export class UploadAddon {

    progress(percentage) {
        $('#myBar').width(percentage + '%');
    };

    humanFileSize(bytes, si=false, dp=1) {
        const thresh = si ? 1000 : 1024;
      
        if (Math.abs(bytes) < thresh) {
          return bytes + ' B';
        }
      
        const units = si 
          ? ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'] 
          : ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
        let u = -1;
        const r = 10**dp;
      
        do {
          bytes /= thresh;
          ++u;
        } while (Math.round(Math.abs(bytes) * r) / r >= thresh && u < units.length - 1);
      
      
        return bytes.toFixed(dp) + ' ' + units[u];
    };
      

    constructor() {
        const self = this;
        // get mod if from query string /upload.html?mod_id=:mod_id
        const mod_id = window.location.href.split('=')[1];
        this.mod_id = mod_id;
        $(document).ready(function(){
            $('form input[type=file]').change(function () {
                let msg = this.files.length + " file(s) selected";
                if (this.files.length > 0) {
                    const file = this.files[0];
                    const fileSize = self.humanFileSize(file.size);
                    msg = `${file.name} ${fileSize}`;
                    if (file.size > 1073741824) {
                        msg = `${file.name} is too large`;
                    } else if (file.size > 314572800) {
                        msg += ` (remember new users are capped at 300 MB)`;
                    }
                }
              $('.file-container p').text(msg);
            });
            Orb.checkSession((loggedIn) => {
                if (!loggedIn && self.mod_id) {
                    window.location.href = '/login_register.html';
                    return;
                }
                if (loggedIn) {
                    $('#upload-addon').show();
                    $('.login-register-link').hide();
                    $('form').attr('action', `/upload_mod?api_key=${Orb.api_key}`);
                    if (typeof self.mod_id !== 'undefined') {
                        Orb.get_mod(self.mod_id, (mod_info) => {
                            self.mod_info = mod_info;
                            if (self.mod_info.is_owner) {
                                $('[name=mod_name]').val(mod_info.name);
                                $('[name=mod_description]').val(mod_info.description);
                                $('[name=orbiter_version]').val(mod_info.orbiter_version);
                                $('[name=mod_version]').val(mod_info.version);
                                $('[name=mod_picture_link]').val(mod_info.picture_link);
                                $('#upload-addon').text('Update');
                            } else {
                               window.location.href = '/upload';
                            }
                        }, (error) => {
                            alert(error.responseJSON.error);
                        }, true);
                    }
                } else {
                    $('#upload-addon').hide();
                    $('.login-register-link').show();
                }
            });

            $("form").submit(function(e) {
                e.preventDefault();    
                var formData = new FormData(this);
                if (typeof self.mod_id !== 'undefined') {
                    formData.append('mod_id', self.mod_id);
                }
                self.progress(0);
                $.ajax({
                    url: `/upload_mod?api_key=${Orb.api_key}`,
                    type: 'POST',
                    data: formData,
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                    
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = evt.loaded / evt.total;
                                percentComplete = parseInt(percentComplete * 100);
                                self.progress(percentComplete);
                            }
                        }, false);
                    
                        return xhr;
                    },
                    success: function (data) {
                        if (data.error) {
                            $('.errors').text(data.error);
                        } else if (data.success) {
                            alert('mod uploaded, you will be redirected automatically.');
                            const id = typeof data.id !== 'undefined' ? data.id : self.mod_id;
                            if (typeof id !== 'undefined') {
                                window.location.href = '/view/' + id + '/' + $('[name=mod_name]').val();
                            }
                            $('.errors').text('mod uploaded');
                        }
                    },
                    error: function(data) {
                        if (data.responseJSON) {
                            $('.errors').text(data.responseJSON.error);
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