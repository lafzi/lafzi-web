<?php

// class untuk dokumen yang "ditemukan"
class found_doc {
    
    var $id;
    var $matched_trigrams_count = 0;
    var $matched_terms_order_score = 0;
    var $matched_terms_count_score = 0;
    var $matched_terms_contiguity_score = 0;
    var $score = 0;
    var $matched_terms = array();
    var $LIS = array();
    
}

// fungsi tambahan untuk pengurut array $matched_docs
function matched_docs_cmp($el1, $el2) {
    
    if ($el1->score == $el2->score) return 0;
    return $el1->score < $el2->score ? 1 : -1;
    
}