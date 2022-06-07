import _ from 'underscore';
import $ from 'jquery';

let addons = {};

$.get('/addons.json', function(data) {
  addons = data;
});

const similarity = (str1 = '', str2 = '') => {
    const track = Array(str2.length + 1).fill(null).map(() =>
    Array(str1.length + 1).fill(null));
    for (let i = 0; i <= str1.length; i += 1) {
       track[0][i] = i;
    }
    for (let j = 0; j <= str2.length; j += 1) {
       track[j][0] = j;
    }
    for (let j = 1; j <= str2.length; j += 1) {
       for (let i = 1; i <= str1.length; i += 1) {
          const indicator = str1[i - 1] === str2[j - 1] ? 0 : 1;
          track[j][i] = Math.min(
             track[j][i - 1] + 1, // deletion
             track[j - 1][i] + 1, // insertion
             track[j - 1][i - 1] + indicator, // substitution
          );
       }
    }
    return track[str2.length][str1.length];
 };

//  String.prototype.fuzzy = function (s) {
//     var hay = this.toLowerCase(), i = 0, n = -1, l;
//     s = s.toLowerCase();
//     for (; l = s[i++] ;) if (!~(n = hay.indexOf(l, n + 1))) return false;
//     return true;
// };

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

function render(results) {
    $('#results').empty();
    results.forEach(function(result) {
        const urls = addons[result];
        for (let inx = 0; inx < urls.length; inx++) {
            const url = urls[inx];
            $('#results').append(`<div><a target="_blank" href="${url}">${result}</a></div>`);
        }
    });
}

function do_search() {
    const search = $('#search').val();
    const results = _.filter(Object.keys(addons), function(item) {
        return item.fuzzy(search, 0.7);
    });
    render(results);
}

const do_search_lazy = _.debounce(do_search, 500);

$('#search').on('keyup', function() {
    do_search_lazy();
});