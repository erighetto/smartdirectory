/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

import '../css/app.scss';
import $ from 'jquery';
import 'bootstrap';
import '@fortawesome/fontawesome-free/css/all.min.css';
import '@fortawesome/fontawesome-free/js/all.js';

const spaghettiError = $("#spaghetti-error");
if (spaghettiError.length > 0) {
    let i = 0, data = "", text = spaghettiError.attr("data-text");
    let typing = setInterval(() => {
        if (i === text.length) {
            clearInterval(typing);
        } else {
            data += text[i];
            spaghettiError.attr("data-text", data);
            i++;
        }
    }, 100);
}
