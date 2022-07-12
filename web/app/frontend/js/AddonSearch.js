import _ from 'underscore';
import $ from 'jquery';

export class AddonSearch {

    get_index(callback, cache_bust=false) {
        let url = '/addons.json';
        if (cache_bust) {
            url += '?cache_bust=' + Math.random();
        }

        $.get(url, (data) => {
            this.addons = data;
            $('#index_count').text(`${Object.keys(this.addons).length} mods in index`);
            document.getElementById('search').focus();
            callback();
            this.do_search();
        });
    };

    constructor() {
        this.addons = {};
        $.get('/mods_count?cache_bust=' + Math.random(), (data) => {
            const index_count = data.count - 2;
            this.get_index(() => {
                if (index_count !== Object.keys(this.addons).length) {
                    this.get_index(() => {}, true)
                }
            });
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
                $('#results').append(`<div><img style="padding-right:10px;" src="/images/of.ico" /><a target="_blank" href="${url}">${result}</a></div>`);
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
            return item.fuzzy(search, 0.7);
        });
        this.render(results);
    };

    run() {
    };
};