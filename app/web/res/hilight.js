Array.max = function( array ){
    return Math.max.apply( Math, array );
};

function inArray(needle, haystack) {
    var length = haystack.length;
    for(var i = 0; i < length; i++) {
        if(haystack[i] == needle) return true;
    }
    return false;
}

function generateHighlightRTL(occArray) {
    max = Array.max(occArray);
    for(var i = 1; i <= max; i++) {
        if(inArray(i, occArray)) {
            document.write("<div class='hl_block on'></div>");
        } else {
            document.write("<div class='hl_block off'></div>");
        }
    }
}
