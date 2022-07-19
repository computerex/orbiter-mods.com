import $ from 'jquery';

export default Orb = {
    user_experiences: [],
    login(username, email, api_key) {
        this.username = username;
        this.email = email;
        this.api_key = api_key;
        window.localStorage.setItem('session', JSON.stringify({
            username: username,
            email: email,
            api_key: api_key
        }));
    },

    logout() {
        window.localStorage.removeItem('session');
        this.username = null;
        this.email = null;
        this.api_key = null;
        this.user_experiences = [];
    },

    checkSession(callback) {
        const session = window.localStorage.getItem('session');
        if (session) {
            const parsed = JSON.parse(session);
            this.username = parsed.username;
            this.email = parsed.email;
            this.api_key = parsed.api_key;

            // call /is_key_valid endpoint to check if api_key is valid
            $.get(`/is_key_valid?api_key=${this.api_key}`, (data) => {
                if (data.success) {
                    callback(true);
                } else {
                    this.logout();
                    callback(false);
                }
            }).fail((err) => {
                this.logout();
                callback(false);
            });
        } else {
            callback(false);
        }
    },

    get_user_experiences(callback) {
        $.get(`/user_experiences?api_key=${this.api_key}`, (data) => {
            if (data.success) {
                this.user_experiences = data.ids;
                callback(data.ids);
            } else {
                alert(data.error);
            }
            callback([]);
        });
    },

    get_mod(mod_id, callbackPass, callbackFail, markdown_desc=false) {
        let url = `/mod/${mod_id}/info?api_key=${Orb.api_key}`;
        if (markdown_desc) {
            url += '&markdown_desc=1';
        }
        $.get(url, (data) => {
            if (callbackPass) {
                callbackPass(data);
            }
        }).fail((data)=> {
            if (callbackFail) {
                callbackFail(data);
            }
        });
    }
};