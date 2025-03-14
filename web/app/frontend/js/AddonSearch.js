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
        this.search_threads = false;

        $('.categories input[type=checkbox]').on('change', (e) => {
            this.do_search_lazy();
        });

        $('input[type=radio][name=search]').on('click', (e) => {
            this.render();
            // Empty #search
            $('#search').val('');
            const id = e.target.id;
        
            // If the "Mods" radio button is clicked
            if (id === 'mods') {
                this.search_threads = false;
                // Set #search placeholder to "Search mods"
                $('#search').attr('placeholder', 'Search mods');
            }
            
            // If the "Messages" radio button is clicked
            else if (id === 'messages') {
                this.search_threads = true;
                $('#search').attr('placeholder', 'press enter to search messages');
            }
        });

        

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
        // enter key on #search
        $('#search').on('keypress', (e) => {
            if (e.which === 13) {
                const phrase = $('#search').val();
                this.search_of_index(phrase);
            }
        });
    };

    get_enabled_categories() {
        const categories_enabled = [];
        $('.categories input[type=checkbox]').each((i, el) => {
            if (el.value === 'orbiter-mods-only') {
                return;
            }
            if (el.checked) {
                categories_enabled.push(el.value);
            }
        });
        return categories_enabled;
    };

    render(results) {
        $('#results').empty();
        if (!results) {
            results = [];
        }
        if (this.search_threads) {
            results.forEach((result) => {
                const author = result['author'];
                const date = new Date(result['date']);
    
                // Format date as DD-MM-YYYY
                const formattedDate = date.getDate() + '-' + (date.getMonth()+1) + '-' + date.getFullYear();

                const id = result['id'];
                const post_id = result['post_id'];
                var text = result['text'];
                const thread_id = result['thread_id'];
                const url = `https://www.orbiter-forum.com/threads/${thread_id}/${post_id}`;

                // Create message result with new styling
                const messageHtml = `
                    <div class="message-result" onclick="window.open('${url}', '_blank')" style="cursor: pointer;">
                        <div class="message-content">
                            ${this.formatMessageContent(text)}
                        </div>
                        <div class="message-meta">
                            <div class="message-date">
                                <span>📅 Posted on ${formattedDate}</span>
                            </div>
                            <div class="message-forum">
                                <span>💬 Orbiter Forum</span>
                            </div>
                        </div>
                    </div>`;

                $('#results').append(messageHtml);
            });   
        } else {
            const orbiter_mods_only = $('#orbiter-mods-only').is(':checked');
            results.forEach((result) => {
                const addon = this.addons[result];
                if (!addon) return;  // Skip if addon not found
                
                const urls = addon['urls'] || [];
                const category = addon['category'] || '';
                
                urls.forEach(url => {
                    if (orbiter_mods_only && !url.match(/orbiter-mods.com/)) {
                        return;
                    }
                    let favico = '/favicon.ico';
                    if (url.indexOf('orbiter-forum.com') > -1) {
                        favico = '/images/of.ico';
                    }
                    $('#results').append(`<div class="result-card">
                        <div class="mod-icon">
                            <img src="${favico}" alt="site icon" style="width: 24px; height: 24px;">
                        </div>
                        <div class="result-content">
                            <div>
                                <a href="${url}" class="mod-title" target="_blank">${result}</a>
                                <span class="mod-category category-${category.toLowerCase()}">
                                    ${this.getCategorySymbol(category)} ${category}
                                </span>
                            </div>
                            <div class="result-meta">
                                <div class="result-stats">
                                    <div class="stat-item">
                                        <span>🔗 ${new URL(url).hostname}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`);
                });
            });
        }
    };

    // Helper function to get category symbol
    getCategorySymbol(category) {
        const symbolMap = {
            'Spacecraft': '🚀',
            'Aircraft': '✈️',
            'Scenarios': '▶️',
            'Utilities': '🔧',
            'Resources': '📦',
            'Sounds': '🔊',
            'Scenery': '🏔️',
            'Launchers': '🛸',
            'Repaints': '🎨',
            'Tutorials': '📚',
            'Miscellaneous': '⭐'
        };
        return symbolMap[category] || '📄';
    }

    // Helper function to format message content with quotes
    formatMessageContent(text) {
        if (text.includes('said:') || text.includes('wrote:')) {
            const parts = text.split(/(?:said:|wrote:)/);
            if (parts.length > 1) {
                const author = parts[0].trim();
                const quote = parts[1].trim();
                const response = parts[2] ? parts[2].trim() : '';

                return `
                    <div class="message-quote">
                        <div class="message-quote-author">${author} wrote:</div>
                        <p>${quote}</p>
                    </div>
                    ${response ? `<p>${response}</p>` : ''}`;
            }
        }
        return `<p>${text}</p>`;
    }

    search_of_index(phrase) {
        // urlencode phrase
        const url_phrase = encodeURIComponent(phrase);
        // GET request /search?phrase=phrase
        $.get('/search?phrase=' + url_phrase, (data) => {
            // render results
            this.render(data);
        });
    };

    do_search() {
        if (this.search_threads) {
            return;
        }

        const search = $('#search').val();
        const enabled_categories = this.get_enabled_categories();

        if (search.length === 0 && enabled_categories.length === 0) {
            this.render();
            return;
        }

        var results = Object.keys(this.addons);

        if (search.length > 0) {
            results = _.filter(results, function(item) {
                return item.fuzzy(search, 0.9);
            });
        }

        
        if (enabled_categories.length > 0) {
            // find all mods with a category that's in enabled_categories
            results = results.filter((key) => {
                if (!this.addons[key]) {
                    return false;
                }
                const category = this.addons[key]['category'] || '';
                return enabled_categories.indexOf(category) > -1;
            });
        }
        this.render(results);
    };

    run() {
    };
};