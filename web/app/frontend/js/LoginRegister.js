import _ from 'underscore';
import $ from 'jquery';

import Orb from './Orb';

const MODE_LOGIN = 'login';
const MODE_REGISTER = 'register';

export class LoginRegister {
    
    constructor(viewer, container, callback) {
        if (viewer) {
            this.viewer = viewer;
        }
        this.mode = MODE_LOGIN;
        this.email = '';
        this.password = '';
        this.parent = '.right-pane';
        if (callback) {
            this.callback = callback;
        }
        if (container) {
            this.parent = container;
        }
    };

    render_mode_login() {
        $(this.parent).append(
            `<div class="login-register-form">
                <div class="login-register-form-input">
                    <label for="email">email:</label>
                    <input id="email" type="text" placeholder="email" />
                </div>
                <div class="login-register-form-input">
                    <label for="password">password:</label>
                    <input id="password" type="password" placeholder="password" />
                </div>
                <br />
                <div class="errors"></div>
                <div class="login-register-form-input">
                    <button id="login">Login</button>
                </div>
                <br />
                <div class="login-register-form-input">
                    <button id="register">Register</button>
                </div>
            </div>`);

        $('#login').on('click', () => {
            // extract email and password
            const email = $('#email').val();
            const password = $('#password').val();
            this.login(email, password);
        });

        $('#register').on('click', () => {
            // extract email and password
            const email = $('#email').val();
            const password = $('#password').val();
            this.register(email, password);
        });
    };

    render_mode_register() {
        $(this.parent).append(
            `<div class="login-register-form">
                <div class="login-register-form-input">
                    <label for="username">username:</label>
                    <input id="username" type="text" placeholder="username" />
                </div>
                <div class="login-register-form-input">
                    <label for="email">email:</label>
                    <input id="email" type="text" placeholder="email" value="${this.email}" />
                </div>
                <div class="login-register-form-input">
                    <label for="password">password:</label>
                    <input id="password" type="password" placeholder="password" value="${this.password}" />
                </div>
                <div class="login-register-form-input">
                    <label for="password2">repeat password:</label>
                    <input id="password2" type="password" placeholder="password" />
                </div>
                <br />
                <div class="errors"></div>
                <div class="login-register-form-input">
                    <button id="register">Register</button>
                </div>
            </div>`);

        $('#register').on('click', () => {
            // extract username and password
            const email = $('#email').val();
            const password = $('#password').val();
            const password2 = $('#password2').val();
            const username = $('#username').val();

            this.register(email, password, password2, username);
        });

    };

    login(email, password) {
        if (!email && !password) {
            return;
        }
        // login by posting to /login endpoint with email and password
        $.post('/login', {
            email: email,
            password: password
        }, (data) => {
            if (data.success) {
                // login successful
                Orb.login(data.username, email, data.api_key);
                if (this.viewer) {
                    this.viewer.render();
                }
                if (this.callback) {
                    this.callback(data);
                }
            } else {
                // login failed
                $('.errors').text(data.error);
                if (this.callback) {
                    this.callback(data);
                }
            }
        }).fail((data) => {
            $('.errors').text(data.responseJSON.error);
        });
    };

    register(email, password, password2, username) {
        // check if password2 and username are passed in
        if (!password2 && !username) {
            this.mode = MODE_REGISTER;
            this.email = email;
            this.password = password;
            this.render();
        }
        // validate email, password, password2, and username
        else if (email && password && password2 && username) {
            // create user by posting to /user endpoint with email, password, and username
            $.post('/user', {
                email: email,
                password: password,
                password2: password2,
                name: username
            }, (data) => {
                // check if user was created
                if (data.success) {
                    Orb.login(username, email, data.api_key);
                    if (this.viewer) {
                        this.viewer.render();
                    }
                    if (this.callback) {
                        this.callback(data);
                    }
                } else {
                    $('.errors').text(data.error);
                    if (this.callback) {
                        this.callback(data);
                    }
                }
            });
        }
    };

    render() {
        $(this.parent).empty();
        $(this.parent).append(`<h1>Login or register</h1>`);

        if (this.mode === MODE_LOGIN) {
            this.render_mode_login();
        } else if (this.mode === MODE_REGISTER) {
            this.render_mode_register();
        }
    };

    run() {
        this.render();
    };
};