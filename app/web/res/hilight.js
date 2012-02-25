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

function generateHighlightRTL(occArray, hlWidth) {
    max = Array.max(occArray);
    for(var i = 1; i <= max; i++) {
        if(inArray(i, occArray)) {
            document.write("<div class='hl_block on' style='width: " + hlWidth + "px'></div>");
        } else {
            document.write("<div class='hl_block off' style='width: " + hlWidth + "px'></div>");
        }
    }
}

function getElementsByClassName(node,classname) {
  if (node.getElementsByClassName) { // use native implementation if available
    return node.getElementsByClassName(classname);
  } else {
    return (function getElementsByClass(searchClass,node) {
        if ( node == null )
          node = document;
        var classElements = [],
            els = node.getElementsByTagName("*"),
            elsLen = els.length,
            pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)"), i, j;

        for (i = 0, j = 0; i < elsLen; i++) {
          if ( pattern.test(els[i].className) ) {
              classElements[j] = els[i];
              j++;
          }
        }
        return classElements;
    })(classname, node);
  }
}

function showHilight() {
    hls = getElementsByClassName(document, 'hl_container');    
    for (var i = 0; i < hls.length; i++) {
        hls[i].style.visibility = 'visible';
    }
}

function hideHilight() {
    hls = getElementsByClassName(document, 'hl_container');    
    for (var i = 0; i < hls.length; i++) {
        hls[i].style.visibility = 'hidden';
    }
}
