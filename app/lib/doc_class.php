<?php

// class untuk dokumen yang "ditemukan"
class found_doc {
    
    var $id;
    var $matched_trigrams_count = 0;
    var $matched_terms = array();
    
}

// fungsi tambahan untuk pengurut array $matched_docs
function matched_docs_cmp($el1, $el2) {
    
    if ($el1->matched_trigrams_count == $el2->matched_trigrams_count) return 0;
    return $el1->matched_trigrams_count < $el2->matched_trigrams_count ? 1 : -1;
    
}