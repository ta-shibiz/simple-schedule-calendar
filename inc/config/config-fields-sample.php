<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function ssc_get_fields() {
    return [
        'base_ub' => 'BASE（全面）',
        'base_u'  => 'BASE Uフィールド',
        'base_b'  => 'BASE Bフィールド',
        'met_lr'  => 'MET（全面）',
        'met_l'   => 'MET Lフィールド',
        'met_r'   => 'MET Rフィールド',
        'base_met'=> 'BASE×MET',
    ];
}

/** Return the sole configured field key as the default. */
function ssc_get_default_field() {
    $fields = ssc_get_fields();

    if ( count( $fields ) !== 1 ) {
        return '';
    }

    foreach ( $fields as $key => $label ) {
        return (string) $key;
    }

    return '';
}

