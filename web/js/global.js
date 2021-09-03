/* ============================================================
 * bootstrap-dropdown.js v2.0.0
 * http://twitter.github.com/bootstrap/javascript.html#dropdowns
 * ============================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */


!function (a) {
    function d() {
        a(b).parent().removeClass("open")
    }

    "use strict";
    var b = '[data-toggle="dropdown"]', c = function (b) {
        var c = a(b).on("click.dropdown.data-api", this.toggle);
        a("html").on("click.dropdown.data-api", function () {
            c.parent().removeClass("open")
        })
    };
    c.prototype = {
        constructor: c, toggle: function (b) {
            var c = a(this), e = c.attr("data-target"), f, g;
            if (!e) {
                e = c.attr("href");
                e = e && e.replace(/.*(?=#[^\s]*$)/, "")
            }
            f = a(e);
            f.length || (f = c.parent());
            g = f.hasClass("open");
            d();
            !g && f.toggleClass("open");
            return false
        }
    };
    a.fn.dropdown = function (b) {
        return this.each(function () {
            var d = a(this), e = d.data("dropdown");
            if (!e) d.data("dropdown", e = new c(this));
            if (typeof b == "string") e[b].call(d)
        })
    };
    a.fn.dropdown.Constructor = c;
    a(function () {
        a("html").on("mouseenter.dropdown.data-api", d);
        a("body").on("mouseover.dropdown.data-api", b, c.prototype.toggle)
    })
}(window.jQuery)

function get_singular(s) {

    if (s.match(/sses$/))
        return s.replace(/sses$/, 'ss');

    if (s.match(/y$/))
        return s.replace(/ies$/, 'y');

    if (s.match(/ies$/))
        return s.replace(/ies$/, 'y');

    if (s.match(/s$/))
        return s.replace(/s$/, '');

    return s;
}

var active_table = active_model = "";

function update_links(table, model) {
    if (model)
        active_model = model;

    if (table)
        active_table = table;
}

String.prototype.sanitizeNamespace = function() {
    return this
    .replace(/(\\{1,})/g,"\\")
    .replace(/(\\\w)/g, function (m, m1) {
        return m1.toUpperCase();
    });
};

String.prototype.capitalize = function () {
    return this.replace(/(^|\s)([a-z])/g, function (m, p1, p2) {
        return p1 + p2.toUpperCase();
    });
};

String.prototype.plural = function (revert) {

    var plural = {
        '(quiz)$': "$1zes",
        '^(ox)$': "$1en",
        '([m|l])ouse$': "$1ice",
        '(matr|vert|ind)ix|ex$': "$1ices",
        '(x|ch|ss|sh)$': "$1es",
        '([^aeiouy]|qu)y$': "$1ies",
        '(hive)$': "$1s",
        '(?:([^f])fe|([lr])f)$': "$1$2ves",
        '(shea|lea|loa|thie)f$': "$1ves",
        'sis$': "ses",
        '([ti])um$': "$1a",
        '(tomat|potat|ech|her|vet)o$': "$1oes",
        '(bu)s$': "$1ses",
        '(alias)$': "$1es",
        '(octop)us$': "$1i",
        '(ax|test)is$': "$1es",
        '(us)$': "$1es",
        '([^s]+)$': "$1s"
    };

    var singular = {
        '(quiz)zes$': "$1",
        '(matr)ices$': "$1ix",
        '(vert|ind)ices$': "$1ex",
        '^(ox)en$': "$1",
        '(alias)es$': "$1",
        '(octop|vir)i$': "$1us",
        '(cris|ax|test)es$': "$1is",
        '(shoe)s$': "$1",
        '(o)es$': "$1",
        '(bus)es$': "$1",
        '([m|l])ice$': "$1ouse",
        '(x|ch|ss|sh)es$': "$1",
        '(m)ovies$': "$1ovie",
        '(s)eries$': "$1eries",
        '([^aeiouy]|qu)ies$': "$1y",
        '([lr])ves$': "$1f",
        '(tive)s$': "$1",
        '(hive)s$': "$1",
        '(li|wi|kni)ves$': "$1fe",
        '(shea|loa|lea|thie)ves$': "$1f",
        '(^analy)ses$': "$1sis",
        '((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$': "$1$2sis",
        '([ti])a$': "$1um",
        '(n)ews$': "$1ews",
        '(h|bl)ouses$': "$1ouse",
        '(corpse)s$': "$1",
        '(us)es$': "$1",
        's$': ""
    };

    var irregular = {
        'move': 'moves',
        'foot': 'feet',
        'goose': 'geese',
        'sex': 'sexes',
        'child': 'children',
        'man': 'men',
        'tooth': 'teeth',
        'person': 'people'
    };

    var uncountable = [
        'sheep',
        'fish',
        'deer',
        'moose',
        'series',
        'species',
        'money',
        'rice',
        'information',
        'equipment'
    ];

    // save some time in the case that singular and plural are the same
    if (uncountable.indexOf(this.toLowerCase()) >= 0)
        return this;

    // check for irregular forms
    for (word in irregular) {

        if (revert) {
            var pattern = new RegExp(irregular[word] + '$', 'i');
            var replace = word;
        } else {
            var pattern = new RegExp(word + '$', 'i');
            var replace = irregular[word];
        }
        if (pattern.test(this))
            return this.replace(pattern, replace);
    }

    if (revert) var array = singular;
    else var array = plural;

    // check for matches using regular expressions
    for (reg in array) {

        var pattern = new RegExp(reg, 'i');

        if (pattern.test(this))
            return this.replace(pattern, array[reg]);
    }

    return this;
}