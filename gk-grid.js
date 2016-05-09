/**
 * 
 * GK Grid front-end JS code
 *
 **/

/*

Copyright 2013-2013 GavickPro (info@gavick.com)

this program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

jQuery(window).load(function(){
    setTimeout(function() {
        jQuery('.gk-grid').each(function(i,el) {
            el = jQuery(el);
            var animation = el.attr('data-animation');
            var animation_random = el.attr('data-random');
            var animation_speed = el.attr('data-speed') == 'normal' ? 500 : (el.attr('data-speed') == 'fast') ? 250 : 750;
            var animation_divider = el.attr('data-speed') == 'normal' ? 4 : (el.attr('data-speed') == 'fast') ? 2 : 6;
            var animation_type = el.attr('data-type');

            if(animation === 'on') {
                var blocks = el.find('.gk-grid-element');

                if(animation_random === 'off') {
                    // linear
                    for(var i = 0, len = blocks.length; i < len; i++) {
                        gkGridAddClass(jQuery(blocks[i]), 'active', i * (animation_speed / animation_divider));
                    }
                } else { // or random animation
                    var randomVector = [];
                    for(var i = 0, len = blocks.length; i < len; i++) {
                        randomVector[i] = i;
                    }
                    randomVector = gkGridShuffle(randomVector);
                    //
                    for(var j = 0, len = blocks.length; j < len; j++) {
                        gkGridAddClass(jQuery(blocks[randomVector[j]]), 'active', j * (animation_speed / animation_divider));
                    }       
                }
                
                setTimeout(function() {
                	jQuery(el.find('.gk-grid-wrap')).addClass('active');
                }, blocks.length * (animation_speed / animation_divider));
            }
        });
    }, 500);
});

function gkGridAddClass(elm, className, delay) {
    setTimeout(function() {
        elm.addClass(className)
    }, delay);
}

function gkGridShuffle(arr){
    for(var j, x, i = arr.length; i; j = Math.floor(Math.random() * i), x = arr[--i], arr[i] = arr[j], arr[j] = x);
    return arr;
};