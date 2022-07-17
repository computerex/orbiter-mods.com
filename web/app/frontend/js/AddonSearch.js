import _ from 'underscore';
import $ from 'jquery';

export class AddonSearch {

    init_search() {
        $('#index_count').text(`${Object.keys(this.addons).length} mods in index`);
        document.getElementById('search').focus();
        this.do_search();
    };

    get_index(callback, cache_bust=false) {
        let url = '/mods.json';
        if (cache_bust) {
            url += '?cache_bust=' + Math.random();
        }

        $.get(url, (data) => {
            this.addons = data;
            callback();
            this.init_search();
        });
    };

    constructor() {
        this.addons = {};
        $.get('/mods_hash?cache_bust=' + Math.random(), (data) => {
            const index_hash = data.hash;
            // set this.addons from local storage
            if (localStorage.getItem('addons') && localStorage.getItem('addons_hash') === index_hash) {
                this.addons = JSON.parse(localStorage.getItem('addons'));
                this.init_search();
                return;
            }
            this.get_index(() => {
                        // save this.addons into local store
                        localStorage.setItem('addons', JSON.stringify(this.addons));
                        localStorage.setItem('addons_hash', index_hash);
                    }, true
            );
        });
        

        this.do_search_lazy = _.debounce(this.do_search, 500);
        String.prototype.fuzzy = function(term, ratio) {
            var string = this.toLowerCase();
            var compare = term.toLowerCase();
            var matches = 0;
            if (string.indexOf(compare) > -1) return true; // covers basic partial matches
            for (var i = 0; i < compare.length; i++) {
                string.indexOf(compare[i]) > -1 ? matches += 1 : matches -=1;
            }
            return (matches/this.length >= ratio || term == "")
        };
        $('#search').on('keyup', () => {
            this.do_search_lazy();
        });
    };

    render(results) {
        $('#results').empty();
        results.forEach((result) => {
            const urls = this.addons[result];
            for (let inx = 0; inx < urls.length; inx++) {
                const url = urls[inx];
                let favico = '/favicon.ico';
                // if url host is orbiter-forum.com change favicon to of.ico
                if (url.indexOf('orbiter-forum.com') > -1) {
                    favico = '/images/of.ico';
                }
                $('#results').append(`<div><img style="padding-right:10px;width:26px;" src="${favico}" /><a target="_blank" href="${url}">${result}</a></div>`);
            }
        });
    };

    do_search() {
        const search = $('#search').val();
        if (search.length === 0) {
            this.render([]);
            return;
        }
        const results = _.filter(Object.keys(this.addons), function(item) {
            return item.fuzzy(search, 0.8);
        });
        this.render(results);
    };

    run() {
    };
};